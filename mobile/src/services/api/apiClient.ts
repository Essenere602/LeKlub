import axios, { AxiosError, InternalAxiosRequestConfig } from 'axios';

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

let refreshPromise: Promise<string | null> | null = null;

apiClient.interceptors.request.use(async (requestConfig) => {
  const token = await tokenStorage.getAccessToken();

  if (token) {
    requestConfig.headers.Authorization = `Bearer ${token}`;
  }

  return requestConfig;
});

apiClient.interceptors.response.use(
  (response) => response,
  async (error: AxiosError) => {
    const originalRequest = error.config as (InternalAxiosRequestConfig & { _retry?: boolean }) | undefined;

    if (
      error.response?.status !== 401 ||
      !originalRequest ||
      originalRequest._retry ||
      originalRequest.url?.includes('/auth/login') ||
      originalRequest.url?.includes('/auth/refresh') ||
      originalRequest.url?.includes('/auth/logout')
    ) {
      return Promise.reject(error);
    }

    originalRequest._retry = true;
    const newAccessToken = await refreshAccessToken();

    if (!newAccessToken) {
      return Promise.reject(error);
    }

    originalRequest.headers.Authorization = `Bearer ${newAccessToken}`;

    return apiClient(originalRequest);
  },
);

async function refreshAccessToken(): Promise<string | null> {
  if (!refreshPromise) {
    refreshPromise = doRefreshAccessToken().finally(() => {
      refreshPromise = null;
    });
  }

  return refreshPromise;
}

async function doRefreshAccessToken(): Promise<string | null> {
  const refreshToken = await tokenStorage.getRefreshToken();

  if (!refreshToken) {
    await tokenStorage.clearTokens();
    return null;
  }

  try {
    const response = await axios.post<{ token: string; refreshToken: string }>(
      `${config.apiBaseUrl}/auth/refresh`,
      { refreshToken },
      {
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
        },
        timeout: 10000,
      },
    );

    await tokenStorage.setTokens(response.data.token, response.data.refreshToken);

    return response.data.token;
  } catch {
    await tokenStorage.clearTokens();
    return null;
  }
}
