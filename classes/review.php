<?php
/**
 * Класс для работы с отзывами
 */
class Review {
    private $conn;
    private $table = 'reviews';

    public $id;
    public $application_id;
    public $user_id;
    public $rating;
    public $comment;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Валидация рейтинга (от 1 до 5)
     */
    public function validateRating($rating) {
        $rating = (int)$rating;
        return $rating >= 1 && $rating <= 5;
    }

    /**
     * Создание отзыва
     */
    public function create() {
        // Проверяем, не оставлял ли уже пользователь отзыв на эту заявку
        $check_query = "SELECT id FROM " . $this->table . " 
                        WHERE application_id = :application_id AND user_id = :user_id LIMIT 1";
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bindParam(':application_id', $this->application_id);
        $check_stmt->bindParam(':user_id', $this->user_id);
        $check_stmt->execute();
        
        if($check_stmt->rowCount() > 0) {
            return [
                'success' => false,
                'message' => 'Вы уже оставили отзыв на эту заявку'
            ];
        }
        
        $query = "INSERT INTO " . $this->table . " 
                  (application_id, user_id, rating, comment) 
                  VALUES (:application_id, :user_id, :rating, :comment)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':application_id', $this->application_id);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':rating', $this->rating);
        $stmt->bindParam(':comment', $this->comment);
        
        if($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Отзыв успешно добавлен',
                'id' => $this->conn->lastInsertId()
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Ошибка при добавлении отзыва'
            ];
        }
    }

    /**
     * Получение отзыва по ID заявки
     */
    public function getReviewByApplicationId($application_id) {
        $query = "SELECT r.*, u.full_name as user_name
                  FROM " . $this->table . " r
                  JOIN users u ON r.user_id = u.id
                  WHERE r.application_id = :application_id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':application_id', $application_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Получение всех отзывов пользователя
     */
    public function getUserReviews($user_id) {
        $query = "SELECT r.*, a.id as application_id, c.name as course_name
                  FROM " . $this->table . " r
                  JOIN applications a ON r.application_id = a.id
                  JOIN courses c ON a.course_id = c.id
                  WHERE r.user_id = :user_id
                  ORDER BY r.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

