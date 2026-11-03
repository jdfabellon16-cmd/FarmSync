import { Navigate, Outlet } from 'react-router-dom';
import { useAuth } from '../auth/AuthContext';

export default function ProtectedRoute() {
    const { user, loading } = useAuth();

    if (loading) {
        return <div className="p-8 text-center text-stone-500">Checking your session…</div>;
    }

    return user ? <Outlet /> : <Navigate to="/login" replace />;
}
