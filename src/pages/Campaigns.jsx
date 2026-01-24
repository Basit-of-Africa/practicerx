import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

const Campaigns = () => {
    const [campaigns, setCampaigns] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showForm, setShowForm] = useState(false);
    const [editingId, setEditingId] = useState(null);
    const [formData, setFormData] = useState({
        name: '',
        description: '',
        trigger_type: 'manual',
        trigger_event: '',
        emails: [{ subject: '', body: '', delay_days: 0 }],
        is_active: true
    });

    useEffect(() => {
        loadCampaigns();
    }, []);

    const loadCampaigns = async () => {
        try {
            const data = await apiFetch({ path: '/ppms/v1/campaigns' });
            setCampaigns(data.data || []);
        } catch (error) {
            console.error('Error loading campaigns:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            if (editingId) {
                await apiFetch({
                    path: `/ppms/v1/campaigns/${editingId}`,
                    method: 'PUT',
                    data: formData
                });
            } else {
                await apiFetch({
                    path: '/ppms/v1/campaigns',
                    method: 'POST',
                    data: formData
                });
            }
            resetForm();
            loadCampaigns();
        } catch (error) {
            alert('Error saving campaign: ' + error.message);
        }
    };

    const handleEdit = (campaign) => {
        setFormData({
            name: campaign.name,
            description: campaign.description || '',
            trigger_type: campaign.trigger_type,
            trigger_event: campaign.trigger_event || '',
            emails: typeof campaign.emails === 'string' ? JSON.parse(campaign.emails) : campaign.emails,
            is_active: campaign.is_active === 1
        });
        setEditingId(campaign.id);
        setShowForm(true);
    };

    const handleDelete = async (id) => {
        if (!confirm('Delete this campaign?')) return;
        try {
            await apiFetch({
                path: `/ppms/v1/campaigns/${id}`,
                method: 'DELETE'
            });
            loadCampaigns();
        } catch (error) {
            alert('Error deleting campaign: ' + error.message);
        }
    };

    const resetForm = () => {
        setFormData({
            name: '',
            description: '',
            trigger_type: 'manual',
            trigger_event: '',
            emails: [{ subject: '', body: '', delay_days: 0 }],
            is_active: true
        });
        setEditingId(null);
        setShowForm(false);
    };

    const addEmailStep = () => {
        setFormData({
            ...formData,
            emails: [...formData.emails, { subject: '', body: '', delay_days: 0 }]
        });
    };

    const removeEmailStep = (index) => {
        const newEmails = formData.emails.filter((_, i) => i !== index);
        setFormData({ ...formData, emails: newEmails });
    };

    const updateEmailStep = (index, field, value) => {
        const newEmails = [...formData.emails];
        newEmails[index][field] = value;
        setFormData({ ...formData, emails: newEmails });
    };

    if (loading) return <div>Loading campaigns...</div>;

    return (
        <div>
            <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '20px' }}>
                <h1>Email Campaigns</h1>
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
                    {showForm ? 'Cancel' : 'New Campaign'}
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
                    <h2>{editingId ? 'Edit Campaign' : 'Create Campaign'}</h2>
                    <form onSubmit={handleSubmit}>
                        <div style={{ marginBottom: '15px' }}>
                            <label style={{ display: 'block', marginBottom: '5px' }}>Campaign Name</label>
                            <input
                                type="text"
                                value={formData.name}
                                onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                                required
                                style={{ width: '100%', padding: '8px', border: '1px solid #ddd', borderRadius: '4px' }}
                            />
                        </div>
                        <div style={{ marginBottom: '15px' }}>
                            <label style={{ display: 'block', marginBottom: '5px' }}>Description</label>
                            <textarea
                                value={formData.description}
                                onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                                rows="3"
                                style={{ width: '100%', padding: '8px', border: '1px solid #ddd', borderRadius: '4px' }}
                            />
                        </div>
                        <div style={{ marginBottom: '15px', display: 'flex', gap: '15px' }}>
                            <div style={{ flex: 1 }}>
                                <label style={{ display: 'block', marginBottom: '5px' }}>Trigger Type</label>
                                <select
                                    value={formData.trigger_type}
                                    onChange={(e) => setFormData({ ...formData, trigger_type: e.target.value })}
                                    style={{ width: '100%', padding: '8px', border: '1px solid #ddd', borderRadius: '4px' }}
                                >
                                    <option value="manual">Manual</option>
                                    <option value="event">Event-triggered</option>
                                </select>
                            </div>
                            {formData.trigger_type === 'event' && (
                                <div style={{ flex: 1 }}>
                                    <label style={{ display: 'block', marginBottom: '5px' }}>Trigger Event</label>
                                    <select
                                        value={formData.trigger_event}
                                        onChange={(e) => setFormData({ ...formData, trigger_event: e.target.value })}
                                        style={{ width: '100%', padding: '8px', border: '1px solid #ddd', borderRadius: '4px' }}
                                    >
                                        <option value="">Select event...</option>
                                        <option value="appointment_booked">Appointment Booked</option>
                                        <option value="program_enrolled">Program Enrolled</option>
                                        <option value="form_submitted">Form Submitted</option>
                                    </select>
                                </div>
                            )}
                        </div>

                        <div style={{ marginBottom: '15px' }}>
                            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '10px' }}>
                                <label style={{ fontWeight: 'bold' }}>Email Sequence</label>
                                <button
                                    type="button"
                                    onClick={addEmailStep}
                                    style={{
                                        padding: '6px 12px',
                                        background: '#00a32a',
                                        color: '#fff',
                                        border: 'none',
                                        borderRadius: '4px',
                                        cursor: 'pointer',
                                        fontSize: '12px'
                                    }}
                                >
                                    + Add Email
                                </button>
                            </div>
                            {formData.emails.map((email, index) => (
                                <div key={index} style={{
                                    padding: '15px',
                                    background: '#f9f9f9',
                                    border: '1px solid #e0e0e0',
                                    borderRadius: '4px',
                                    marginBottom: '10px'
                                }}>
                                    <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '10px' }}>
                                        <strong>Email {index + 1}</strong>
                                        {formData.emails.length > 1 && (
                                            <button
                                                type="button"
                                                onClick={() => removeEmailStep(index)}
                                                style={{
                                                    padding: '4px 8px',
                                                    background: '#dc3232',
                                                    color: '#fff',
                                                    border: 'none',
                                                    borderRadius: '4px',
                                                    cursor: 'pointer',
                                                    fontSize: '11px'
                                                }}
                                            >
                                                Remove
                                            </button>
                                        )}
                                    </div>
                                    <div style={{ marginBottom: '10px' }}>
                                        <label style={{ display: 'block', marginBottom: '5px', fontSize: '13px' }}>
                                            Delay (days)
                                        </label>
                                        <input
                                            type="number"
                                            min="0"
                                            value={email.delay_days}
                                            onChange={(e) => updateEmailStep(index, 'delay_days', parseInt(e.target.value))}
                                            style={{ width: '100px', padding: '6px', border: '1px solid #ddd', borderRadius: '4px' }}
                                        />
                                    </div>
                                    <div style={{ marginBottom: '10px' }}>
                                        <label style={{ display: 'block', marginBottom: '5px', fontSize: '13px' }}>Subject</label>
                                        <input
                                            type="text"
                                            value={email.subject}
                                            onChange={(e) => updateEmailStep(index, 'subject', e.target.value)}
                                            required
                                            placeholder="Use {{first_name}}, {{last_name}}, etc."
                                            style={{ width: '100%', padding: '6px', border: '1px solid #ddd', borderRadius: '4px' }}
                                        />
                                    </div>
                                    <div>
                                        <label style={{ display: 'block', marginBottom: '5px', fontSize: '13px' }}>Body</label>
                                        <textarea
                                            value={email.body}
                                            onChange={(e) => updateEmailStep(index, 'body', e.target.value)}
                                            required
                                            rows="4"
                                            placeholder="Email content. Use merge tags: {{first_name}}, {{last_name}}, {{email}}, {{phone}}"
                                            style={{ width: '100%', padding: '6px', border: '1px solid #ddd', borderRadius: '4px' }}
                                        />
                                    </div>
                                </div>
                            ))}
                        </div>

                        <div style={{ marginBottom: '15px' }}>
                            <label style={{ display: 'flex', alignItems: 'center', cursor: 'pointer' }}>
                                <input
                                    type="checkbox"
                                    checked={formData.is_active}
                                    onChange={(e) => setFormData({ ...formData, is_active: e.target.checked })}
                                    style={{ marginRight: '8px' }}
                                />
                                Active
                            </label>
                        </div>

                        <div style={{ display: 'flex', gap: '10px' }}>
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
                                {editingId ? 'Update' : 'Create'} Campaign
                            </button>
                            <button
                                type="button"
                                onClick={resetForm}
                                style={{
                                    padding: '10px 20px',
                                    background: '#ddd',
                                    color: '#333',
                                    border: 'none',
                                    borderRadius: '4px',
                                    cursor: 'pointer'
                                }}
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            )}

            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(300px, 1fr))', gap: '20px' }}>
                {campaigns.length === 0 ? (
                    <div style={{ padding: '40px', textAlign: 'center', color: '#666' }}>
                        No campaigns created yet
                    </div>
                ) : (
                    campaigns.map(campaign => (
                        <div key={campaign.id} style={{
                            background: '#fff',
                            border: '1px solid #ddd',
                            borderRadius: '4px',
                            padding: '20px'
                        }}>
                            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'start', marginBottom: '10px' }}>
                                <h3 style={{ margin: 0 }}>{campaign.name}</h3>
                                <span style={{
                                    padding: '4px 8px',
                                    borderRadius: '4px',
                                    fontSize: '11px',
                                    background: campaign.is_active ? '#00aa00' : '#999',
                                    color: '#fff'
                                }}>
                                    {campaign.is_active ? 'Active' : 'Inactive'}
                                </span>
                            </div>
                            <p style={{ color: '#666', fontSize: '13px', marginBottom: '15px' }}>
                                {campaign.description || 'No description'}
                            </p>
                            <div style={{ fontSize: '12px', color: '#666', marginBottom: '15px' }}>
                                <div>Trigger: {campaign.trigger_type}</div>
                                <div>Emails: {campaign.emails ? (typeof campaign.emails === 'string' ? JSON.parse(campaign.emails).length : campaign.emails.length) : 0}</div>
                            </div>
                            <div style={{ display: 'flex', gap: '10px' }}>
                                <button
                                    onClick={() => handleEdit(campaign)}
                                    style={{
                                        padding: '6px 12px',
                                        background: '#0073aa',
                                        color: '#fff',
                                        border: 'none',
                                        borderRadius: '4px',
                                        cursor: 'pointer',
                                        fontSize: '12px'
                                    }}
                                >
                                    Edit
                                </button>
                                <button
                                    onClick={() => handleDelete(campaign.id)}
                                    style={{
                                        padding: '6px 12px',
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
                            </div>
                        </div>
                    ))
                )}
            </div>
        </div>
    );
};

export default Campaigns;
