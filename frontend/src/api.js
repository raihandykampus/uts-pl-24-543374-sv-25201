import axios from 'axios';

// Base API configuration (relative /api for Nginx reverse proxy)
const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: 15000,
});

// Request interceptor: attach bearer token if present
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Response interceptor: handle 401 Unauthorized (session expired / revoked token)
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response && error.response.status === 401) {
      localStorage.removeItem('auth_token');
      localStorage.removeItem('auth_user');
      window.dispatchEvent(new CustomEvent('auth:unauthorized'));
    }
    return Promise.reject(error);
  }
);

export const api = {
  // Authentication
  async login(email, password) {
    const response = await apiClient.post('/login', { email, password });
    const { token, user } = response.data;
    if (token) {
      localStorage.setItem('auth_token', token);
    }
    if (user) {
      localStorage.setItem('auth_user', JSON.stringify(user));
    }
    return response.data;
  },

  async logout() {
    try {
      await apiClient.post('/logout');
    } finally {
      localStorage.removeItem('auth_token');
      localStorage.removeItem('auth_user');
    }
  },

  async getUser() {
    const response = await apiClient.get('/user');
    return response.data;
  },

  // Task CRUD operations
  async getTasks() {
    const response = await apiClient.get('/tasks');
    return response.data;
  },

  async getTask(id) {
    const response = await apiClient.get(`/tasks/${id}`);
    return response.data;
  },

  async createTask(data) {
    const response = await apiClient.post('/tasks', data);
    return response.data;
  },

  async updateTask(id, data) {
    const response = await apiClient.put(`/tasks/${id}`, data);
    return response.data;
  },

  async deleteTask(id) {
    const response = await apiClient.delete(`/tasks/${id}`);
    return response.data;
  },

  // Auth state helpers
  getToken() {
    return localStorage.getItem('auth_token');
  },

  getUserData() {
    try {
      return JSON.parse(localStorage.getItem('auth_user'));
    } catch {
      return null;
    }
  },

  isAuthenticated() {
    return Boolean(localStorage.getItem('auth_token'));
  },

  clearAuth() {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('auth_user');
  },
};

export default api;
