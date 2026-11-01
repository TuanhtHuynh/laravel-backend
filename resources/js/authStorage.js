// authStorage.js - lưu auth data từ BE JWT vào localStorage cho FE React
// Đảm bảo UTF-8: nội dung tiếng Việt lưu đúng, không bị lỗi ký tự

const STORAGE_KEY = 'auth_data';

export const saveAuthData = (data) => {
    try {
        const payload = {
            token: data.token || null,
            token_type: data.token_type || 'bearer',
            user: data.user || null,
            role: data.role || null,
            permissions: data.permissions || [],
            savedAt: new Date().toISOString(),
        };
        localStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
    } catch (e) {
        console.error('Lỗi lưu auth vào localStorage:', e);
    }
};

export const getToken = () => {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        if (!raw) return null;
        const data = JSON.parse(raw);
        return data.token || null;
    } catch (e) {
        console.error('Lỗi đọc token từ localStorage:', e);
        return null;
    }
};

export const getAuthData = () => {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        if (!raw) return null;
        return JSON.parse(raw);
    } catch (e) {
        console.error('Lỗi đọc auth từ localStorage:', e);
        return null;
    }
};

export const getUser = () => {
    const data = getAuthData();
    return data ? data.user : null;
};

export const getRole = () => {
    const data = getAuthData();
    return data ? data.role : null;
};

export const getPermissions = () => {
    const data = getAuthData();
    return data ? data.permissions : [];
};

export const clearAuthData = () => {
    localStorage.removeItem(STORAGE_KEY);
};

export default {
    saveAuthData,
    getToken,
    getAuthData,
    getUser,
    getRole,
    getPermissions,
    clearAuthData,
};
