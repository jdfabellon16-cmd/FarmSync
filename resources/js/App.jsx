import React from 'react';
import { createRoot } from 'react-dom/client';
import 'leaflet/dist/leaflet.css';
import FarmSyncApp from './FarmSyncApp.jsx';

createRoot(document.getElementById('app')).render(
    <React.StrictMode>
        <FarmSyncApp />
    </React.StrictMode>,
);
