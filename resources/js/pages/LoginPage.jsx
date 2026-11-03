import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { z } from 'zod';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { getApiError } from '../api';
import { useAuth } from '../auth/AuthContext';

const loginSchema = z.object({
    email: z.string().email('Enter a valid email address.'),
    password: z.string().min(1, 'Enter your password.'),
});

const registerSchema = loginSchema.extend({
    name: z.string().min(2, 'Enter your name.').max(120),
    password: z.string().min(8, 'Use at least 8 characters.'),
    password_confirmation: z.string().min(8, 'Confirm your password.'),
}).refine((values) => values.password === values.password_confirmation, {
    message: 'Passwords do not match.',
    path: ['password_confirmation'],
});

export default function LoginPage() {
    const [registering, setRegistering] = useState(false);
    const [serverError, setServerError] = useState('');
    const [submitting, setSubmitting] = useState(false);
    const { signIn } = useAuth();
    const navigate = useNavigate();
    const schema = registering ? registerSchema : loginSchema;
    const { register, handleSubmit, formState: { errors } } = useForm({
        resolver: zodResolver(schema),
    });

    async function onSubmit(values) {
        setSubmitting(true);
        setServerError('');

        try {
            await signIn(values, registering);
            navigate('/farms', { replace: true });
        } catch (error) {
            setServerError(getApiError(error));
        } finally {
            setSubmitting(false);
        }
    }

    return (
        <main className="flex min-h-screen items-center justify-center bg-[#f5f7f1] px-4 py-10">
            <section className="w-full max-w-md rounded-3xl border border-stone-200 bg-white p-7 shadow-sm sm:p-9">
                <div className="mb-8">
                    <div className="mb-5 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-green-800 text-xl text-white">F</div>
                    <p className="text-sm font-semibold uppercase tracking-[0.18em] text-green-800">FarmSync</p>
                    <h1 className="mt-2 text-3xl font-semibold tracking-tight text-stone-900">
                        {registering ? 'Create your farm account' : 'Welcome back'}
                    </h1>
                    <p className="mt-2 text-sm text-stone-500">Plan your fields and keep your farm moving.</p>
                </div>

                {serverError && <p role="alert" className="mb-5 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-800">{serverError}</p>}

                <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
                    {registering && (
                        <label className="block text-sm font-medium text-stone-700">
                            Your name
                            <input {...register('name')} autoComplete="name" className="form-input" />
                            {errors.name && <span className="form-error">{errors.name.message}</span>}
                        </label>
                    )}
                    <label className="block text-sm font-medium text-stone-700">
                        Email
                        <input {...register('email')} type="email" autoComplete="email" className="form-input" />
                        {errors.email && <span className="form-error">{errors.email.message}</span>}
                    </label>
                    <label className="block text-sm font-medium text-stone-700">
                        Password
                        <input {...register('password')} type="password" autoComplete={registering ? 'new-password' : 'current-password'} className="form-input" />
                        {errors.password && <span className="form-error">{errors.password.message}</span>}
                    </label>
                    {registering && (
                        <label className="block text-sm font-medium text-stone-700">
                            Confirm password
                            <input {...register('password_confirmation')} type="password" autoComplete="new-password" className="form-input" />
                            {errors.password_confirmation && <span className="form-error">{errors.password_confirmation.message}</span>}
                        </label>
                    )}
                    <button disabled={submitting} className="w-full rounded-xl bg-green-800 px-4 py-3 font-semibold text-white transition hover:bg-green-900 disabled:opacity-60">
                        {submitting ? 'Please wait…' : registering ? 'Create account' : 'Sign in'}
                    </button>
                </form>

                <button
                    type="button"
                    onClick={() => {
                        setRegistering(!registering);
                        setServerError('');
                    }}
                    className="mt-5 w-full text-center text-sm font-medium text-green-800 hover:underline"
                >
                    {registering ? 'Already have an account? Sign in' : 'New to FarmSync? Create an account'}
                </button>
            </section>
        </main>
    );
}
