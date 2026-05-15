import axios from 'axios';

import { config } from '../../config/env';
import { tokenStorage } from '../auth/tokenStorage';

export const apiClient = axios.create({
  baseURL: config.apiBaseUrl,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
  timeout: 10000,
});

apiClient.interceptors.request.use(async (requestConfig) => {
  const token = await tokenStorage.getAccessToken();

  if (token) {
    requestConfig.headers.Authorization = `Bearer ${token}`;
  }

  return requestConfig;
});
