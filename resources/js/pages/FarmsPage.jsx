import { useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { z } from 'zod';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { Link } from 'react-router-dom';
import { api, getApiError } from '../api';
import { useAuth } from '../auth/AuthContext';
import FieldMap from '../components/FieldMap';

const farmSchema = z.object({
    name: z.string().min(1, 'Enter a farm name.').max(150),
    location: z.string().max(255).optional(),
    latitude: z.union([z.literal(''), z.coerce.number().min(-90).max(90)]).optional(),
    longitude: z.union([z.literal(''), z.coerce.number().min(-180).max(180)]).optional(),
    total_area_hectares: z.coerce.number().positive('Area must be greater than zero.'),
});

const fieldSchema = z.object({
    name: z.string().min(1, 'Enter a field name.').max(100),
    area_hectares: z.coerce.number().positive('Area must be greater than zero.'),
    soil_type: z.string().max(100).optional(),
    irrigation_type: z.string().max(100).optional(),
    status: z.enum(['Fallow', 'Prepared', 'Planted', 'Growing', 'Harvested']),
    latitude: z.union([z.literal(''), z.coerce.number().min(-90).max(90)]).optional(),
    longitude: z.union([z.literal(''), z.coerce.number().min(-180).max(180)]).optional(),
});

const statuses = ['Fallow', 'Prepared', 'Planted', 'Growing', 'Harvested'];

function FarmForm({ onClose }) {
    const queryClient = useQueryClient();
    const [error, setError] = useState('');
    const { register, handleSubmit, formState: { errors, isSubmitting } } = useForm({
        resolver: zodResolver(farmSchema),
        defaultValues: { name: '', location: '', latitude: '', longitude: '', total_area_hectares: '' },
    });

    async function save(values) {
        setError('');

        try {
            await api.post('/farms', {
                ...values,
                latitude: values.latitude === '' ? null : values.latitude,
                longitude: values.longitude === '' ? null : values.longitude,
            });
            await queryClient.invalidateQueries({ queryKey: ['farms'] });
            onClose();
        } catch (requestError) {
            setError(getApiError(requestError));
        }
    }

    return (
        <form onSubmit={handleSubmit(save)} className="grid gap-4 rounded-2xl border border-green-200 bg-green-50/60 p-5 sm:grid-cols-2">
            <label className="form-label">Farm name<input {...register('name')} className="form-input" />{errors.name && <span className="form-error">{errors.name.message}</span>}</label>
            <label className="form-label">Location<input {...register('location')} placeholder="Town, region" className="form-input" /></label>
            <label className="form-label">Total area (ha)<input {...register('total_area_hectares')} type="number" step="0.0001" className="form-input" />{errors.total_area_hectares && <span className="form-error">{errors.total_area_hectares.message}</span>}</label>
            <div className="grid grid-cols-2 gap-3">
                <label className="form-label">Latitude<input {...register('latitude')} type="number" step="any" className="form-input" />{errors.latitude && <span className="form-error">{errors.latitude.message}</span>}</label>
                <label className="form-label">Longitude<input {...register('longitude')} type="number" step="any" className="form-input" />{errors.longitude && <span className="form-error">{errors.longitude.message}</span>}</label>
            </div>
            {error && <p role="alert" className="text-sm text-red-800 sm:col-span-2">{error}</p>}
            <div className="flex gap-3 sm:col-span-2">
                <button disabled={isSubmitting} className="rounded-xl bg-green-800 px-5 py-2.5 text-sm font-semibold text-white disabled:opacity-60">{isSubmitting ? 'Saving…' : 'Save farm'}</button>
                <button type="button" onClick={onClose} className="rounded-xl border border-stone-300 px-5 py-2.5 text-sm font-semibold text-stone-700">Cancel</button>
            </div>
        </form>
    );
}

function FieldForm({ farm, onClose }) {
    const queryClient = useQueryClient();
    const [error, setError] = useState('');
    const { register, handleSubmit, formState: { errors, isSubmitting } } = useForm({
        resolver: zodResolver(fieldSchema),
        defaultValues: {
            name: '',
            area_hectares: '',
            soil_type: '',
            irrigation_type: 'Rain-fed',
            status: 'Fallow',
            latitude: '',
            longitude: '',
        },
    });

    async function save(values) {
        setError('');

        try {
            await api.post(`/farms/${farm.id}/fields`, {
                ...values,
                latitude: values.latitude === '' ? null : values.latitude,
                longitude: values.longitude === '' ? null : values.longitude,
            });
            await queryClient.invalidateQueries({ queryKey: ['fields', farm.id] });
            await queryClient.invalidateQueries({ queryKey: ['farms'] });
            onClose();
        } catch (requestError) {
            setError(getApiError(requestError));
        }
    }

    return (
        <form onSubmit={handleSubmit(save)} className="grid gap-3 rounded-xl bg-stone-50 p-4 sm:grid-cols-2">
            <label className="form-label">Field name<input {...register('name')} className="form-input" />{errors.name && <span className="form-error">{errors.name.message}</span>}</label>
            <label className="form-label">Area (ha)<input {...register('area_hectares')} type="number" step="0.0001" className="form-input" />{errors.area_hectares && <span className="form-error">{errors.area_hectares.message}</span>}</label>
            <label className="form-label">Soil type<input {...register('soil_type')} placeholder="e.g. Loam" className="form-input" /></label>
            <label className="form-label">Irrigation<select {...register('irrigation_type')} className="form-input"><option>Rain-fed</option><option>Drip</option><option>Sprinkler</option><option>Furrow</option><option>Other</option></select></label>
            <label className="form-label sm:col-span-2">Status<select {...register('status')} className="form-input">{statuses.map((status) => <option key={status}>{status}</option>)}</select></label>
            <label className="form-label">Latitude<input {...register('latitude')} type="number" step="any" className="form-input" />{errors.latitude && <span className="form-error">{errors.latitude.message}</span>}</label>
            <label className="form-label">Longitude<input {...register('longitude')} type="number" step="any" className="form-input" />{errors.longitude && <span className="form-error">{errors.longitude.message}</span>}</label>
            {error && <p role="alert" className="text-sm text-red-800 sm:col-span-2">{error}</p>}
            <div className="flex gap-3 sm:col-span-2">
                <button disabled={isSubmitting} className="rounded-lg bg-green-800 px-4 py-2 text-sm font-semibold text-white disabled:opacity-60">{isSubmitting ? 'Saving…' : 'Add field'}</button>
                <button type="button" onClick={onClose} className="rounded-lg border border-stone-300 px-4 py-2 text-sm font-semibold text-stone-700">Cancel</button>
            </div>
        </form>
    );
}

function FarmCard({ farm }) {
    const [addingField, setAddingField] = useState(false);
    const fieldsQuery = useQuery({
        queryKey: ['fields', farm.id],
        queryFn: async () => (await api.get(`/farms/${farm.id}/fields`, { params: { per_page: 100 } })).data.data,
    });
    const fields = fieldsQuery.data ?? [];

    return (
        <article className="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
            <div className="grid gap-5 p-5 lg:grid-cols-[1fr_1.15fr] lg:p-6">
                <div className="flex min-w-0 flex-col">
                    <div className="flex items-start justify-between gap-3">
                        <div>
                            <h2 className="text-xl font-semibold text-stone-900">{farm.name}</h2>
                            <p className="mt-1 text-sm text-stone-500">{farm.location || 'Location not set'}</p>
                        </div>
                        <span className="shrink-0 rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-800">{Number(farm.total_area_hectares).toLocaleString()} ha</span>
                    </div>
                    <div className="my-5 grid grid-cols-2 gap-3">
                        <div className="rounded-xl bg-stone-50 p-3">
                            <p className="text-xs font-medium uppercase tracking-wide text-stone-500">Fields</p>
                            <p className="mt-1 text-2xl font-semibold">{farm.fields_count ?? fields.length}</p>
                        </div>
                        <div className="rounded-xl bg-stone-50 p-3">
                            <p className="text-xs font-medium uppercase tracking-wide text-stone-500">In production</p>
                            <p className="mt-1 text-2xl font-semibold">{fields.filter((field) => ['Planted', 'Growing'].includes(field.status)).length}</p>
                        </div>
                    </div>
                    {fieldsQuery.isError && <p role="alert" className="mb-3 text-sm text-red-800">Could not load fields. Refresh to try again.</p>}
                    <div className="mb-4 space-y-2">
                        {fieldsQuery.isLoading && <p className="text-sm text-stone-500">Loading fields…</p>}
                        {fields.map((field) => (
                            <div key={field.id} className="flex items-center justify-between gap-3 rounded-xl border border-stone-100 px-3 py-2.5">
                                <div className="min-w-0">
                                    <p className="truncate text-sm font-semibold text-stone-800">{field.name}</p>
                                    <p className="text-xs text-stone-500">{field.soil_type || 'Soil not set'} · {field.irrigation_type || 'No irrigation set'}</p>
                                </div>
                                <div className="flex shrink-0 items-center gap-2">
                                    {farm.can_manage_fields ? (
                                        <FieldStatus farm={farm} field={field} />
                                    ) : (
                                        <span className={`rounded-full px-2.5 py-1 text-xs font-medium ${field.status === 'Growing' || field.status === 'Planted' ? 'bg-green-50 text-green-800' : 'bg-amber-50 text-amber-800'}`}>{field.status}</span>
                                    )}
                                    <span className="text-xs text-stone-500">{Number(field.area_hectares).toLocaleString()} ha</span>
                                </div>
                            </div>
                        ))}
                        {!fieldsQuery.isLoading && fields.length === 0 && <p className="text-sm text-stone-500">No fields added yet.</p>}
                    </div>
                    {!farm.can_manage_fields ? null : addingField ? <FieldForm farm={farm} onClose={() => setAddingField(false)} /> : (
                        <button onClick={() => setAddingField(true)} className="mt-auto self-start rounded-xl border border-green-800 px-4 py-2 text-sm font-semibold text-green-800 hover:bg-green-50">+ Add field</button>
                    )}
                </div>
                <div className="min-h-64 overflow-hidden rounded-xl">
                    <FieldMap farm={farm} fields={fields} />
                </div>
            </div>
        </article>
    );
}

function FieldStatus({ farm, field }) {
    const queryClient = useQueryClient();
    const updateStatus = useMutation({
        mutationFn: (status) => api.patch(`/farms/${farm.id}/fields/${field.id}`, { status }),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: ['fields', farm.id] }),
    });

    return (
        <div>
            <select
                aria-label={`Status for ${field.name}`}
                value={field.status}
                onChange={(event) => updateStatus.mutate(event.target.value)}
                className="max-w-32 rounded-full border-0 bg-green-50 py-1 pl-2.5 pr-7 text-xs font-medium text-green-800 focus:ring-2 focus:ring-green-700"
            >
                {statuses.map((status) => <option key={status}>{status}</option>)}
            </select>
            {updateStatus.isError && <p className="text-xs text-red-700">{getApiError(updateStatus.error)}</p>}
        </div>
    );
}

export default function FarmsPage() {
    const [addingFarm, setAddingFarm] = useState(false);
    const [search, setSearch] = useState('');
    const { user, signOut } = useAuth();
    const queryClient = useQueryClient();
    const farmsQuery = useQuery({
        queryKey: ['farms', search],
        queryFn: async () => (await api.get('/farms', { params: { search, per_page: 50 } })).data.data,
    });
    const farmCount = farmsQuery.data?.length ?? 0;
    const fieldCount = farmsQuery.data?.reduce((total, farm) => total + (farm.fields_count ?? 0), 0) ?? 0;
    const canCreateFarm = user?.roles?.some((role) => ['Admin', 'Farm Owner'].includes(role)) ?? false;

    async function logout() {
        try {
            await signOut();
        } finally {
            queryClient.clear();
        }
    }

    return (
        <main className="min-h-screen">
            <header className="border-b border-stone-200 bg-white">
                <div className="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                    <Link to="/farms" className="flex items-center gap-3">
                        <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-green-800 font-semibold text-white">F</span>
                        <span><span className="block font-semibold text-stone-900">FarmSync</span><span className="block text-xs text-stone-500">Farm operations</span></span>
                    </Link>
                    <div className="flex items-center gap-3">
                        <span className="hidden text-sm text-stone-600 sm:inline">{user?.name}</span>
                        <button onClick={logout} className="rounded-lg border border-stone-300 px-3 py-2 text-sm font-medium text-stone-700 hover:bg-stone-50">Sign out</button>
                    </div>
                </div>
            </header>

            <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <section className="mb-8 rounded-3xl bg-gradient-to-br from-green-900 to-green-800 px-6 py-7 text-white sm:px-8 sm:py-9">
                    <p className="text-sm font-semibold uppercase tracking-[0.16em] text-green-200">Farm overview</p>
                    <div className="mt-2 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
                        <div>
                            <h1 className="text-3xl font-semibold tracking-tight sm:text-4xl">Your farms, in sync.</h1>
                            <p className="mt-2 max-w-xl text-sm text-green-100 sm:text-base">Manage your farm locations, field details, soil and irrigation in one place.</p>
                        </div>
                        {canCreateFarm && <button onClick={() => setAddingFarm(!addingFarm)} className="shrink-0 rounded-xl bg-white px-5 py-3 text-sm font-semibold text-green-900 shadow-sm hover:bg-green-50">+ Add a farm</button>}
                    </div>
                </section>

                {addingFarm && canCreateFarm && <div className="mb-6"><FarmForm onClose={() => setAddingFarm(false)} /></div>}

                <section className="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <div className="rounded-2xl border border-stone-200 bg-white p-4 sm:p-5">
                        <p className="text-xs font-medium uppercase tracking-wide text-stone-500">Farms</p>
                        <p className="mt-1 text-2xl font-semibold">{farmCount}</p>
                    </div>
                    <div className="rounded-2xl border border-stone-200 bg-white p-4 sm:p-5">
                        <p className="text-xs font-medium uppercase tracking-wide text-stone-500">Fields</p>
                        <p className="mt-1 text-2xl font-semibold">{fieldCount}</p>
                    </div>
                    <div className="col-span-2 rounded-2xl border border-stone-200 bg-white p-4 sm:col-span-1 sm:p-5">
                        <p className="text-xs font-medium uppercase tracking-wide text-stone-500">Your role</p>
                        <p className="mt-1 truncate text-base font-semibold">{user?.roles?.[0] ?? 'Farm user'}</p>
                    </div>
                </section>

                <section>
                    <div className="mb-4 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                        <div><h2 className="text-xl font-semibold">Farm locations</h2><p className="mt-1 text-sm text-stone-500">Your farms and their active fields.</p></div>
                        <input value={search} onChange={(event) => setSearch(event.target.value)} aria-label="Search farms" placeholder="Search farms…" className="w-full rounded-xl border border-stone-300 bg-white px-4 py-2.5 text-sm outline-none ring-green-700 focus:ring-2 sm:max-w-xs" />
                    </div>
                    {farmsQuery.isLoading && <p className="rounded-2xl border border-stone-200 bg-white p-8 text-center text-stone-500">Loading your farms…</p>}
                    {farmsQuery.isError && <p role="alert" className="rounded-2xl bg-red-50 p-5 text-sm text-red-800">We could not load your farms. Refresh the page to try again.</p>}
                    {farmsQuery.isSuccess && farmsQuery.data.length === 0 && (
                        <div className="rounded-2xl border border-dashed border-stone-300 bg-white px-6 py-12 text-center">
                            <h3 className="font-semibold text-stone-900">{search ? 'No matching farms' : 'No farms added yet'}</h3>
                            <p className="mt-1 text-sm text-stone-500">{search ? 'Try another search.' : 'Add your first farm to get started.'}</p>
                        </div>
                    )}
                    <div className="space-y-4">
                        {farmsQuery.data?.map((farm) => <FarmCard key={farm.id} farm={farm} />)}
                    </div>
                </section>
            </div>
        </main>
    );
}
