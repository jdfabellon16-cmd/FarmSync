import { createContext, useContext, useEffect, useMemo, useState } from 'react';
import { api } from '../api';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (!sessionStorage.getItem('farmsync_token')) {
            setLoading(false);

            return;
        }

        api.get('/auth/me')
            .then(({ data }) => setUser(data.data))
            .catch(() => {
                sessionStorage.removeItem('farmsync_token');
                setUser(null);
            })
            .finally(() => setLoading(false));
    }, []);

    async function signIn(credentials, isRegistration) {
        const { data } = await api.post(
            isRegistration ? '/auth/register' : '/auth/login',
            credentials,
        );
        sessionStorage.setItem('farmsync_token', data.data.token);
        setUser(data.data.user);
    }

    async function signOut() {
        try {
            await api.post('/auth/logout');
        } finally {
            sessionStorage.removeItem('farmsync_token');
            setUser(null);
        }
    }

    const value = useMemo(() => ({
        user,
        loading,
        signIn,
        signOut,
    }), [user, loading]);

    return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
    return useContext(AuthContext);
}
