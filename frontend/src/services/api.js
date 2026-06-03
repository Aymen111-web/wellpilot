import axios from 'axios';

const getBackendUrl = () => {
  if (typeof window !== 'undefined') {
    const stored = localStorage.getItem('wellpilot_backend_url');
    if (stored && (stored.startsWith('http://') || stored.startsWith('https://'))) {
      return stored;
    }
    // Dynamic fallback to port 8000 on the same host
    const protocol = window.location.protocol;
    const hostname = window.location.hostname || '127.0.0.1';
    return `${protocol}//${hostname}:8000`;
  }
  return 'http://127.0.0.1:8000';
};

const backendUrl = getBackendUrl();

const api = axios.create({
  baseURL: `${backendUrl}/api`,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

export default api;
export { backendUrl };
