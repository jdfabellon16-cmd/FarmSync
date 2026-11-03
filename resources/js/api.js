import axios from 'axios';

export const api = axios.create({
    baseURL: '/api/v1',
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    },
});

api.interceptors.request.use((config) => {
    const token = sessionStorage.getItem('farmsync_token');

    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
});

export function getApiError(error) {
    return error.response?.data?.message ?? 'Could not reach FarmSync. Check your connection and try again.';
}
