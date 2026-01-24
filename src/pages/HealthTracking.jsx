import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

const HealthTracking = () => {
    const [metrics, setMetrics] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showForm, setShowForm] = useState(false);
    const [formData, setFormData] = useState({
        client_id: '',
        metric_type: 'weight',
        value: '',
        unit: 'kg',
        notes: ''
    });

    useEffect(() => {
        loadMetrics();
    }, []);

    const loadMetrics = async () => {
        try {
            const data = await apiFetch({ path: '/ppms/v1/health-metrics' });
            setMetrics(data.data || []);
        } catch (error) {
            console.error('Error loading metrics:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            await apiFetch({
                path: '/ppms/v1/health-metrics',
                method: 'POST',
                data: formData
            });
            setShowForm(false);
            setFormData({
                client_id: '',
                metric_type: 'weight',
                value: '',
                unit: 'kg',
                notes: ''
            });
            loadMetrics();
        } catch (error) {
            alert('Error recording metric: ' + error.message);
        }
    };

    const handleDelete = async (id) => {
        if (!confirm('Delete this metric?')) return;
        try {
            await apiFetch({
                path: `/ppms/v1/health-metrics/${id}`,
                method: 'DELETE'
            });
            loadMetrics();
        } catch (error) {
            alert('Error deleting metric: ' + error.message);
        }
    };

    const getMetricIcon = (type) => {
        const icons = {
            weight: '⚖️',
            blood_pressure: '💉',
            heart_rate: '❤️',
            temperature: '🌡️',
            blood_sugar: '🩸',
            cholesterol: '📊',
            other: '📈'
        };
        return icons[type] || '📊';
    };

    if (loading) return <div>Loading health metrics...</div>;

    return (
        <div>
            <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '20px' }}>
                <h1>Health Tracking</h1>
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
                    {showForm ? 'Cancel' : 'Record Metric'}
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
                    <h2>Record Health Metric</h2>
                    <form onSubmit={handleSubmit}>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '15px' }}>
                            <div>
                                <label style={{ display: 'block', marginBottom: '5px' }}>Client ID</label>
                                <input
                                    type="number"
                                    value={formData.client_id}
                                    onChange={(e) => setFormData({ ...formData, client_id: e.target.value })}
                                    required
                                    style={{ width: '100%', padding: '8px', border: '1px solid #ddd', borderRadius: '4px' }}
                                />
                            </div>
                            <div>
                                <label style={{ display: 'block', marginBottom: '5px' }}>Metric Type</label>
                                <select
                                    value={formData.metric_type}
                                    onChange={(e) => setFormData({ ...formData, metric_type: e.target.value })}
                                    style={{ width: '100%', padding: '8px', border: '1px solid #ddd', borderRadius: '4px' }}
                                >
                                    <option value="weight">Weight</option>
                                    <option value="blood_pressure">Blood Pressure</option>
                                    <option value="heart_rate">Heart Rate</option>
                                    <option value="temperature">Temperature</option>
                                    <option value="blood_sugar">Blood Sugar</option>
                                    <option value="cholesterol">Cholesterol</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div style={{ display: 'grid', gridTemplateColumns: '2fr 1fr', gap: '15px', marginTop: '15px' }}>
                            <div>
                                <label style={{ display: 'block', marginBottom: '5px' }}>Value</label>
                                <input
                                    type="text"
                                    value={formData.value}
                                    onChange={(e) => setFormData({ ...formData, value: e.target.value })}
                                    required
                                    placeholder="e.g., 70 or 120/80"
                                    style={{ width: '100%', padding: '8px', border: '1px solid #ddd', borderRadius: '4px' }}
                                />
                            </div>
                            <div>
                                <label style={{ display: 'block', marginBottom: '5px' }}>Unit</label>
                                <input
                                    type="text"
                                    value={formData.unit}
                                    onChange={(e) => setFormData({ ...formData, unit: e.target.value })}
                                    placeholder="e.g., kg, mmHg"
                                    style={{ width: '100%', padding: '8px', border: '1px solid #ddd', borderRadius: '4px' }}
                                />
                            </div>
                        </div>
                        <div style={{ marginTop: '15px' }}>
                            <label style={{ display: 'block', marginBottom: '5px' }}>Notes (optional)</label>
                            <textarea
                                value={formData.notes}
                                onChange={(e) => setFormData({ ...formData, notes: e.target.value })}
                                rows="3"
                                style={{ width: '100%', padding: '8px', border: '1px solid #ddd', borderRadius: '4px' }}
                            />
                        </div>
                        <button
                            type="submit"
                            style={{
                                marginTop: '15px',
                                padding: '10px 20px',
                                background: '#0073aa',
                                color: '#fff',
                                border: 'none',
                                borderRadius: '4px',
                                cursor: 'pointer'
                            }}
                        >
                            Record Metric
                        </button>
                    </form>
                </div>
            )}

            <div style={{ background: '#fff', border: '1px solid #ddd', borderRadius: '4px', overflow: 'hidden' }}>
                <table style={{ width: '100%', borderCollapse: 'collapse' }}>
                    <thead>
                        <tr style={{ background: '#f7f7f7' }}>
                            <th style={{ padding: '12px', textAlign: 'left', borderBottom: '1px solid #ddd' }}>Type</th>
                            <th style={{ padding: '12px', textAlign: 'left', borderBottom: '1px solid #ddd' }}>Client</th>
                            <th style={{ padding: '12px', textAlign: 'left', borderBottom: '1px solid #ddd' }}>Value</th>
                            <th style={{ padding: '12px', textAlign: 'left', borderBottom: '1px solid #ddd' }}>Unit</th>
                            <th style={{ padding: '12px', textAlign: 'left', borderBottom: '1px solid #ddd' }}>Recorded</th>
                            <th style={{ padding: '12px', textAlign: 'left', borderBottom: '1px solid #ddd' }}>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {metrics.length === 0 ? (
                            <tr>
                                <td colSpan="6" style={{ padding: '20px', textAlign: 'center', color: '#666' }}>
                                    No health metrics recorded
                                </td>
                            </tr>
                        ) : (
                            metrics.map(metric => (
                                <tr key={metric.id} style={{ borderBottom: '1px solid #f0f0f0' }}>
                                    <td style={{ padding: '12px' }}>
                                        <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                                            <span>{getMetricIcon(metric.metric_type)}</span>
                                            <span>{metric.metric_type.replace('_', ' ')}</span>
                                        </div>
                                    </td>
                                    <td style={{ padding: '12px' }}>Client #{metric.client_id}</td>
                                    <td style={{ padding: '12px', fontWeight: 'bold' }}>{metric.value}</td>
                                    <td style={{ padding: '12px' }}>{metric.unit}</td>
                                    <td style={{ padding: '12px' }}>{metric.recorded_at}</td>
                                    <td style={{ padding: '12px' }}>
                                        <button
                                            onClick={() => handleDelete(metric.id)}
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
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
            </div>

            {metrics.length > 0 && (
                <div style={{
                    marginTop: '20px',
                    padding: '20px',
                    background: '#fff',
                    border: '1px solid #ddd',
                    borderRadius: '4px',
                    textAlign: 'center',
                    color: '#666'
                }}>
                    📊 Interactive charts and trend analysis coming soon
                </div>
            )}
        </div>
    );
};

export default HealthTracking;
