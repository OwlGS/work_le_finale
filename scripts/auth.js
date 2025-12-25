window.API_URL = 'http://localhost:3000/api';
const API_URL = window.API_URL;

const AuthService = {
    saveToken(token) {
        localStorage.setItem('auth_token', token);
    },

    getToken() {
        return localStorage.getItem('auth_token');
    },

    removeToken() {
        localStorage.removeItem('auth_token');
    },

    saveUser(user) {
        localStorage.setItem('user_data', JSON.stringify(user));
    },

    getUser() {
        const data = localStorage.getItem('user_data');
        return data ? JSON.parse(data) : null;
    },

    logout() {
        this.removeToken();
        localStorage.removeItem('user_data');
        window.location.href = '/login.html';
    },

    async checkAuth() {
        const token = this.getToken();
        if (!token) return false;

        try {
            const response = await fetch(`${API_URL}/auth/verify`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.saveUser(data.user);
                return true;
            } else {
                this.removeToken();
                return false;
            }
        } catch (error) {
            console.error('Ошибка проверки авторизации:', error);
            return false;
        }
    },

    getAuthHeaders() {
        const token = this.getToken();
        return {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        };
    }
};

