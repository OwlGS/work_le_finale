<?php
/**
 * Класс для работы с заявками на обучение
 */
class Application {
    private $conn;
    private $table = 'applications';

    public $id;
    public $user_id;
    public $course_id;
    public $start_date;
    public $payment_method;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Валидация даты
     * Формат: ДД.ММ.ГГГГ
     */
    public function validateDate($date) {
        if(empty($date)) {
            return false;
        }
        // Проверяем формат ДД.ММ.ГГГГ
        if(!preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $date)) {
            return false;
        }
        
        // Парсим дату
        $parts = explode('.', $date);
        $day = (int)$parts[0];
        $month = (int)$parts[1];
        $year = (int)$parts[2];
        
        // Проверяем корректность даты
        if(!checkdate($month, $day, $year)) {
            return false;
        }
        
        // Проверяем, что дата не в прошлом
        $date_obj = new DateTime($year . '-' . $month . '-' . $day);
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        
        return $date_obj >= $today;
    }

    /**
     * Конвертация даты из формата ДД.ММ.ГГГГ в YYYY-MM-DD для БД
     */
    private function convertDateToDB($date) {
        $parts = explode('.', $date);
        return $parts[2] . '-' . $parts[1] . '-' . $parts[0];
    }

    /**
     * Конвертация даты из БД в формат ДД.ММ.ГГГГ
     */
    public function convertDateFromDB($date) {
        if(empty($date)) {
            return '';
        }
        $parts = explode('-', $date);
        return $parts[2] . '.' . $parts[1] . '.' . $parts[0];
    }

    /**
     * Создание новой заявки
     */
    public function create() {
        // Конвертируем дату в формат БД
        $db_date = $this->convertDateToDB($this->start_date);
        
        $query = "INSERT INTO " . $this->table . " 
                  (user_id, course_id, start_date, payment_method) 
                  VALUES (:user_id, :course_id, :start_date, :payment_method)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':course_id', $this->course_id);
        $stmt->bindParam(':start_date', $db_date);
        $stmt->bindParam(':payment_method', $this->payment_method);
        
        if($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Заявка успешно отправлена на рассмотрение',
                'id' => $this->conn->lastInsertId()
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Ошибка при создании заявки'
            ];
        }
    }

    /**
     * Получение всех заявок пользователя
     */
    public function getUserApplications($user_id) {
        $query = "SELECT a.*, c.name as course_name, c.description as course_description
                  FROM " . $this->table . " a
                  JOIN courses c ON a.course_id = c.id
                  WHERE a.user_id = :user_id
                  ORDER BY a.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Конвертируем даты обратно в формат ДД.ММ.ГГГГ
        foreach($applications as &$app) {
            $app['start_date'] = $this->convertDateFromDB($app['start_date']);
            $app['created_at'] = date('d.m.Y H:i', strtotime($app['created_at']));
        }
        
        return $applications;
    }

    /**
     * Получение всех заявок для администратора
     */
    public function getAllApplications($filters = []) {
        $query = "SELECT a.*, c.name as course_name, u.full_name as user_name, u.phone, u.email
                  FROM " . $this->table . " a
                  JOIN courses c ON a.course_id = c.id
                  JOIN users u ON a.user_id = u.id";
        
        $conditions = [];
        $params = [];
        
        // Фильтр по статусу
        if(!empty($filters['status'])) {
            $conditions[] = "a.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        // Фильтр по курсу
        if(!empty($filters['course_id'])) {
            $conditions[] = "a.course_id = :course_id";
            $params[':course_id'] = $filters['course_id'];
        }
        
        if(!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $query .= " ORDER BY a.created_at DESC";
        
        // Пагинация
        $page = isset($filters['page']) ? (int)$filters['page'] : 1;
        $per_page = isset($filters['per_page']) ? (int)$filters['per_page'] : 10;
        $offset = ($page - 1) * $per_page;
        
        $query .= " LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        
        foreach($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        
        $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Конвертируем даты
        foreach($applications as &$app) {
            $app['start_date'] = $this->convertDateFromDB($app['start_date']);
            $app['created_at'] = date('d.m.Y H:i', strtotime($app['created_at']));
        }
        
        return $applications;
    }

    /**
     * Получение количества заявок для пагинации
     */
    public function getApplicationsCount($filters = []) {
        $query = "SELECT COUNT(*) as total
                  FROM " . $this->table . " a";
        
        $conditions = [];
        $params = [];
        
        if(!empty($filters['status'])) {
            $conditions[] = "a.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if(!empty($filters['course_id'])) {
            $conditions[] = "a.course_id = :course_id";
            $params[':course_id'] = $filters['course_id'];
        }
        
        if(!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $stmt = $this->conn->prepare($query);
        
        foreach($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (int)$result['total'];
    }

    /**
     * Обновление статуса заявки
     */
    public function updateStatus($id, $status) {
        $allowed_statuses = ['new', 'in_progress', 'completed'];
        
        if(!in_array($status, $allowed_statuses)) {
            return [
                'success' => false,
                'message' => 'Недопустимый статус'
            ];
        }
        
        $query = "UPDATE " . $this->table . " 
                  SET status = :status 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        
        if($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Статус заявки обновлён'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Ошибка при обновлении статуса'
            ];
        }
    }

    /**
     * Получение заявки по ID
     */
    public function getApplicationById($id) {
        $query = "SELECT a.*, c.name as course_name, u.full_name as user_name
                  FROM " . $this->table . " a
                  JOIN courses c ON a.course_id = c.id
                  JOIN users u ON a.user_id = u.id
                  WHERE a.id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $app = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($app) {
            $app['start_date'] = $this->convertDateFromDB($app['start_date']);
        }
        
        return $app;
    }

    /**
     * Проверка, может ли пользователь оставить отзыв
     * Отзыв можно оставить только если статус заявки "completed"
     */
    public function canLeaveReview($application_id, $user_id) {
        $query = "SELECT status FROM " . $this->table . " 
                  WHERE id = :id AND user_id = :user_id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $application_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result && $result['status'] === 'completed';
    }
}
?>

