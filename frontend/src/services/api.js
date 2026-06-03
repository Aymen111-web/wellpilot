import axios from 'axios';

// Get base URL for backend API: check window location or fall back to localhost
const backendUrl = localStorage.getItem('wellpilot_backend_url') || 'http://127.0.0.1:8000';

const api = axios.create({
  baseURL: `${backendUrl}/api`,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

export default api;
export { backendUrl };
