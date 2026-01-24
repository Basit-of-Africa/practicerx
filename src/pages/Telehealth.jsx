import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

const Telehealth = () => {
    const [sessions, setSessions] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showForm, setShowForm] = useState(false);
    const [formData, setFormData] = useState({
        client_id: '',
        practitioner_id: '',
        appointment_id: '',
        provider: 'zoom',
        scheduled_for: ''
    });

    useEffect(() => {
        loadSessions();
    }, []);

    const loadSessions = async () => {
        try {
            const data = await apiFetch({ path: '/ppms/v1/telehealth/sessions' });
            setSessions(data.data || []);
        } catch (error) {
            console.error('Error loading sessions:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            await apiFetch({
                path: '/ppms/v1/telehealth/sessions',
                method: 'POST',
                data: formData
            });
            setShowForm(false);
            setFormData({
                client_id: '',
                practitioner_id: '',
                appointment_id: '',
                provider: 'zoom',
                scheduled_for: ''
            });
            loadSessions();
        } catch (error) {
            alert('Error creating session: ' + error.message);
        }
    };

    const handleEndSession = async (id) => {
        if (!confirm('End this session?')) return;
        try {
            await apiFetch({
                path: `/ppms/v1/telehealth/sessions/${id}/end`,
                method: 'POST'
            });
            loadSessions();
        } catch (error) {
            alert('Error ending session: ' + error.message);
        }
    };

    const getStatusBadge = (status) => {
        const colors = {
            scheduled: '#ffa500',
            'in-progress': '#00aa00',
            completed: '#0066cc',
            cancelled: '#cc0000'
        };
        return (
            <span style={{
                padding: '4px 8px',
                borderRadius: '4px',
                fontSize: '12px',
                color: '#fff',
                background: colors[status] || '#666'
            }}>
                {status}
            </span>
        );
    };

    if (loading) return <div>Loading sessions...</div>;

    return (
        <div>
            <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '20px' }}>
                <h1>Telehealth Sessions</h1>
                <button
                    onClick={() => setShowForm(!showForm)}
                    style={{
                        padding: '10px 20px',
                        background: '#0073aa',
                        color: '#fff',
                        border: 'none',
                        borderRadius: '4px',
                        cursor: 'pointer'
                    }}
                >
                    {showForm ? 'Cancel' : 'Schedule Session'}
                </button>
            </div>

            {showForm && (
                <div style={{
                    background: '#fff',
                    padding: '20px',
                    marginBottom: '20px',
                    border: '1px solid #ddd',
                    borderRadius: '4px'
                }}>
                    <h2>Schedule Video Session</h2>
                    <form onSubmit={handleSubmit}>
                        <div style={{ marginBottom: '15px' }}>
                            <label style={{ display: 'block', marginBottom: '5px' }}>Client ID</label>
                            <input
                                type="number"
                                value={formData.client_id}
                                onChange={(e) => setFormData({ ...formData, client_id: e.target.value })}
                                required
                                style={{ width: '100%', padding: '8px', border: '1px solid #ddd', borderRadius: '4px' }}
                            />
                        </div>
                        <div style={{ marginBottom: '15px' }}>
                            <label style={{ display: 'block', marginBottom: '5px' }}>Practitioner ID</label>
                            <input
                                type="number"
                                value={formData.practitioner_id}
                                onChange={(e) => setFormData({ ...formData, practitioner_id: e.target.value })}
                                required
                                style={{ width: '100%', padding: '8px', border: '1px solid #ddd', borderRadius: '4px' }}
                            />
                        </div>
                        <div style={{ marginBottom: '15px' }}>
                            <label style={{ display: 'block', marginBottom: '5px' }}>Appointment ID (optional)</label>
                            <input
                                type="number"
                                value={formData.appointment_id}
                                onChange={(e) => setFormData({ ...formData, appointment_id: e.target.value })}
                                style={{ width: '100%', padding: '8px', border: '1px solid #ddd', borderRadius: '4px' }}
                            />
                        </div>
                        <div style={{ marginBottom: '15px' }}>
                            <label style={{ display: 'block', marginBottom: '5px' }}>Provider</label>
                            <select
                                value={formData.provider}
                                onChange={(e) => setFormData({ ...formData, provider: e.target.value })}
                                style={{ width: '100%', padding: '8px', border: '1px solid #ddd', borderRadius: '4px' }}
                            >
                                <option value="zoom">Zoom</option>
                                <option value="twilio">Twilio Video</option>
                            </select>
                        </div>
                        <div style={{ marginBottom: '15px' }}>
                            <label style={{ display: 'block', marginBottom: '5px' }}>Scheduled For</label>
                            <input
                                type="datetime-local"
                                value={formData.scheduled_for}
                                onChange={(e) => setFormData({ ...formData, scheduled_for: e.target.value })}
                                required
                                style={{ width: '100%', padding: '8px', border: '1px solid #ddd', borderRadius: '4px' }}
                            />
                        </div>
                        <button
                            type="submit"
                            style={{
                                padding: '10px 20px',
                                background: '#0073aa',
                                color: '#fff',
                                border: 'none',
                                borderRadius: '4px',
                                cursor: 'pointer'
                            }}
                        >
                            Create Session
                        </button>
                    </form>
                </div>
            )}

            <div style={{ background: '#fff', border: '1px solid #ddd', borderRadius: '4px', overflow: 'hidden' }}>
                <table style={{ width: '100%', borderCollapse: 'collapse' }}>
                    <thead>
                        <tr style={{ background: '#f7f7f7' }}>
                            <th style={{ padding: '12px', textAlign: 'left', borderBottom: '1px solid #ddd' }}>ID</th>
                            <th style={{ padding: '12px', textAlign: 'left', borderBottom: '1px solid #ddd' }}>Client</th>
                            <th style={{ padding: '12px', textAlign: 'left', borderBottom: '1px solid #ddd' }}>Provider</th>
                            <th style={{ padding: '12px', textAlign: 'left', borderBottom: '1px solid #ddd' }}>Scheduled</th>
                            <th style={{ padding: '12px', textAlign: 'left', borderBottom: '1px solid #ddd' }}>Status</th>
                            <th style={{ padding: '12px', textAlign: 'left', borderBottom: '1px solid #ddd' }}>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {sessions.length === 0 ? (
                            <tr>
                                <td colSpan="6" style={{ padding: '20px', textAlign: 'center', color: '#666' }}>
                                    No sessions scheduled
                                </td>
                            </tr>
                        ) : (
                            sessions.map(session => (
                                <tr key={session.id} style={{ borderBottom: '1px solid #f0f0f0' }}>
                                    <td style={{ padding: '12px' }}>{session.id}</td>
                                    <td style={{ padding: '12px' }}>Client #{session.client_id}</td>
                                    <td style={{ padding: '12px' }}>{session.provider}</td>
                                    <td style={{ padding: '12px' }}>{session.scheduled_for}</td>
                                    <td style={{ padding: '12px' }}>{getStatusBadge(session.status)}</td>
                                    <td style={{ padding: '12px' }}>
                                        {session.join_url && (
                                            <a
                                                href={session.join_url}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                style={{
                                                    marginRight: '10px',
                                                    color: '#0073aa',
                                                    textDecoration: 'none'
                                                }}
                                            >
                                                Join
                                            </a>
                                        )}
                                        {session.status === 'in-progress' && (
                                            <button
                                                onClick={() => handleEndSession(session.id)}
                                                style={{
                                                    padding: '4px 12px',
                                                    background: '#dc3232',
                                                    color: '#fff',
                                                    border: 'none',
                                                    borderRadius: '4px',
                                                    cursor: 'pointer',
                                                    fontSize: '12px'
                                                }}
                                            >
                                                End
                                            </button>
                                        )}
                                    </td>
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
};

export default Telehealth;
