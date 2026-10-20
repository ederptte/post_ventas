import axios from 'axios';

const api = axios.create({
  // Coloca aquí la URL base de tu servidor backend
  baseURL: 'http://localhost:8000/api',
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json'
  }
});

export default api;
