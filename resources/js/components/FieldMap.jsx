import { CircleMarker, MapContainer, Popup, TileLayer } from 'react-leaflet';

export default function FieldMap({ farm, fields }) {
    if (!farm.latitude || !farm.longitude) {
        return (
            <div className="flex h-64 items-center justify-center rounded-xl bg-stone-100 text-sm text-stone-500">
                Add farm coordinates to show the field map.
            </div>
        );
    }

    const center = [Number(farm.latitude), Number(farm.longitude)];

    return (
        <MapContainer
            center={center}
            zoom={12}
            scrollWheelZoom={false}
            className="h-64 rounded-xl"
            key={`farm-${farm.id}-${center.join('-')}`}
        >
            <TileLayer
                attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
            />
            <CircleMarker center={center} radius={9} pathOptions={{ color: '#166534', fillOpacity: 0.9 }}>
                <Popup>{farm.name}</Popup>
            </CircleMarker>
            {fields.filter((field) => field.latitude && field.longitude).map((field) => (
                <CircleMarker
                    key={field.id}
                    center={[Number(field.latitude), Number(field.longitude)]}
                    radius={7}
                    pathOptions={{ color: '#d97706', fillOpacity: 0.9 }}
                >
                    <Popup>{field.name} · {field.status}</Popup>
                </CircleMarker>
            ))}
        </MapContainer>
    );
}
