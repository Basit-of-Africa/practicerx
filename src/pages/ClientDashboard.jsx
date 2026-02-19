import ClientLayout from '../components/ClientLayout';
import { useState, useEffect } from '@wordpress/element';

const ClientDashboard = () => {
    const [appointments, setAppointments] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');

    useEffect(() => {
        const token = localStorage.getItem('ppms_token');
        const headers = { 'Content-Type': 'application/json' };
        if (token) headers['Authorization'] = 'Bearer ' + token;

        fetch(practicerxSettings.root + 'appointments/client', {
            method: 'GET',
            headers,
        }).then(async (res) => {
            if (!res.ok) {
                const body = await res.json().catch(() => ({}));
                setError(body.message || 'Failed to load appointments');
                setLoading(false);
                return;
            }

            const data = await res.json();
            setAppointments(data);
            setLoading(false);
        }).catch(() => {
            setError('Network error');
            setLoading(false);
        });
    }, []);

    return (
        <ClientLayout>
            <div style={{ padding: 20 }}>
                <h1>Dashboard</h1>

                <div className="prx-cards">
                    <div className="prx-card">
                        <div style={{ fontSize: 12, color: '#6b7280' }}>Upcoming</div>
                        <div style={{ fontSize: 20, fontWeight: 700 }}>{Array.isArray(appointments) ? appointments.length : '—'}</div>
                    </div>
                    <div className="prx-card">
                        <div style={{ fontSize: 12, color: '#6b7280' }}>Next Appointment</div>
                        <div style={{ fontSize: 16, fontWeight: 600 }}>{Array.isArray(appointments) && appointments[0] ? new Date(appointments[0].start_time).toLocaleString() : '—'}</div>
                    </div>
                    <div className="prx-card">
                        <div style={{ fontSize: 12, color: '#6b7280' }}>Messages</div>
                        <div style={{ fontSize: 20, fontWeight: 700 }}>0</div>
                    </div>
                </div>

                <section>
                    <h2>Appointments</h2>
                    {loading && <div>Loading…</div>}
                    {error && <div style={{ color: 'red' }}>{error}</div>}
                    {!loading && !error && (
                        <div>
                            {Array.isArray(appointments) && appointments.length ? (
                                <ul style={{ listStyle: 'none', padding: 0 }}>
                                    {appointments.map((a) => (
                                        <li key={a.id} style={{ padding: 12, borderBottom: '1px solid #eee' }}>
                                            <div style={{ fontWeight: 600 }}>{a.title || 'Appointment'}</div>
                                            <div>{new Date(a.start_time).toLocaleString()}</div>
                                            <div style={{ color: '#666' }}>{a.status}</div>
                                        </li>
                                    ))}
                                </ul>
                            ) : (
                                <div>No appointments found.</div>
                            )}
                        </div>
                    )}
                </section>
            </div>
        </ClientLayout>
    );
};

export default ClientDashboard;
