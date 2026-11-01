// Ví dụ React: dùng authStorage.js để lưu user/token từ API login
// Lưu ý UTF-8: file lưu đúng encoding, tiếng Việt hiển thị đúng

import { saveAuthData, getToken, clearAuthData, getUser } from './authStorage';

// Gọi sau khi API /auth/login thành công
// saveAuthData(response.data);

export const apiClient = () => {
    const token = getToken();
    return {
        headers: token
            ? { Authorization: `Bearer ${token}`, 'Content-Type': 'application/json; charset=utf-8' }
            : { 'Content-Type': 'application/json; charset=utf-8' },
    };
};

export const AuthExample = () => {
    const user = getUser();
    if (!user) return null;
    return (
        <div>
            <h2>Xin chào, {user.name || 'Người dùng'}</h2>
            <p>Vai trò: {user.staff?.role?.slug || 'khách'}</p>
        </div>
    );
};
