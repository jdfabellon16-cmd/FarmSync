import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';
import { AuthProvider, useAuth } from './auth/AuthContext';
import ProtectedRoute from './components/ProtectedRoute';
import FarmsPage from './pages/FarmsPage';
import LoginPage from './pages/LoginPage';

const queryClient = new QueryClient({
    defaultOptions: {
        queries: {
            retry: 1,
            refetchOnWindowFocus: false,
            staleTime: 30_000,
        },
    },
});

function AppRoutes() {
    const { user } = useAuth();

    return (
        <Routes>
            <Route path="/login" element={user ? <Navigate to="/farms" replace /> : <LoginPage />} />
            <Route element={<ProtectedRoute />}>
                <Route path="/farms" element={<FarmsPage />} />
            </Route>
            <Route path="*" element={<Navigate to={user ? '/farms' : '/login'} replace />} />
        </Routes>
    );
}

export default function FarmSyncApp() {
    return (
        <QueryClientProvider client={queryClient}>
            <AuthProvider>
                <BrowserRouter>
                    <AppRoutes />
                </BrowserRouter>
            </AuthProvider>
        </QueryClientProvider>
    );
}
