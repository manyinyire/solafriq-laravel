import axios from 'axios';

/**
 * Base API configuration
 */
const api = axios.create({
  baseURL: '/',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

/**
 * Request interceptor for adding CSRF token
 */
api.interceptors.request.use(
  (config) => {
    const token = document.head.querySelector('meta[name="csrf-token"]');
    if (token) {
      config.headers['X-CSRF-TOKEN'] = token.content;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

/**
 * Response interceptor for handling errors
 */
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response) {
      // Handle specific error codes
      switch (error.response.status) {
        case 401:
          // Redirect to login if unauthorized
          window.location.href = '/login';
          break;
        case 403:
          console.error('Forbidden: You do not have permission to perform this action');
          break;
        case 404:
          console.error('Resource not found');
          break;
        case 422:
          // Validation errors - handled by caller
          break;
        case 500:
          console.error('Server error occurred');
          break;
        default:
          console.error('An error occurred:', error.response.data.message);
      }
    }
    return Promise.reject(error);
  }
);

export default api;
