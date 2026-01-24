import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

const Forms = () => {
    const [forms, setForms] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showBuilder, setShowBuilder] = useState(false);
    const [editingId, setEditingId] = useState(null);
    const [formData, setFormData] = useState({
        name: '',
        description: '',
        form_type: 'intake',
        fields: [{ name: '', type: 'text', label: '', required: false, options: '' }]
    });

    useEffect(() => {
        loadForms();
    }, []);

    const loadForms = async () => {
        try {
            const data = await apiFetch({ path: '/ppms/v1/forms' });
            setForms(data.data || []);
        } catch (error) {
            console.error('Error loading forms:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            const payload = {
                ...formData,
                practitioner_id: window.practicerxSettings?.currentUserId || 1
            };
            if (editingId) {
                await apiFetch({
                    path: `/ppms/v1/forms/${editingId}`,
                    method: 'PUT',
                    data: payload
                });
            } else {
                await apiFetch({
                    path: '/ppms/v1/forms',
                    method: 'POST',
                    data: payload
                });
            }
            resetForm();
            loadForms();
        } catch (error) {
            alert('Error saving form: ' + error.message);
        }
    };

    const handleEdit = (form) => {
        setFormData({
            name: form.name,
            description: form.description || '',
            form_type: form.form_type,
            fields: typeof form.fields === 'string' ? JSON.parse(form.fields) : form.fields
        });
        setEditingId(form.id);
        setShowBuilder(true);
    };

    const handleDelete = async (id) => {
        if (!confirm('Delete this form?')) return;
        try {
            await apiFetch({
                path: `/ppms/v1/forms/${id}`,
                method: 'DELETE'
            });
            loadForms();
        } catch (error) {
            alert('Error deleting form: ' + error.message);
        }
    };

    const resetForm = () => {
        setFormData({
            name: '',
            description: '',
            form_type: 'intake',
            fields: [{ name: '', type: 'text', label: '', required: false, options: '' }]
        });
        setEditingId(null);
        setShowBuilder(false);
    };

    const addField = () => {
        setFormData({
            ...formData,
            fields: [...formData.fields, { name: '', type: 'text', label: '', required: false, options: '' }]
        });
    };

    const removeField = (index) => {
        const newFields = formData.fields.filter((_, i) => i !== index);
        setFormData({ ...formData, fields: newFields });
    };

    const updateField = (index, key, value) => {
        const newFields = [...formData.fields];
        newFields[index][key] = value;
        setFormData({ ...formData, fields: newFields });
    };

    if (loading) return <div>Loading forms...</div>;

    return (
        <div>
            <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '20px' }}>
                <h1>Forms Builder</h1>
                <button
                    onClick={() => setShowBuilder(!showBuilder)}
                    style={{
                        padding: '10px 20px',
                        background: '#0073aa',
                        color: '#fff',
                        border: 'none',
                        borderRadius: '4px',
                        cursor: 'pointer'
                    }}
                >
                    {showBuilder ? 'Cancel' : 'Create Form'}
                </button>
            </div>

            {showBuilder && (
                <div style={{
                    background: '#fff',
                    padding: '20px',
                    marginBottom: '20px',
                    border: '1px solid #ddd',
                    borderRadius: '4px'
                }}>
                    <h2>{editingId ? 'Edit Form' : 'Create Form'}</h2>
                    <form onSubmit={handleSubmit}>
                        <div style={{ marginBottom: '15px' }}>
                            <label style={{ display: 'block', marginBottom: '5px' }}>Form Name</label>
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
                                rows="2"
                                style={{ width: '100%', padding: '8px', border: '1px solid #ddd', borderRadius: '4px' }}
                            />
                        </div>
                        <div style={{ marginBottom: '15px' }}>
                            <label style={{ display: 'block', marginBottom: '5px' }}>Form Type</label>
                            <select
                                value={formData.form_type}
                                onChange={(e) => setFormData({ ...formData, form_type: e.target.value })}
                                style={{ width: '100%', padding: '8px', border: '1px solid #ddd', borderRadius: '4px' }}
                            >
                                <option value="intake">Intake Form</option>
                                <option value="assessment">Assessment</option>
                                <option value="questionnaire">Questionnaire</option>
                                <option value="consent">Consent Form</option>
                            </select>
                        </div>

                        <div style={{ marginBottom: '15px' }}>
                            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '10px' }}>
                                <label style={{ fontWeight: 'bold' }}>Form Fields</label>
                                <button
                                    type="button"
                                    onClick={addField}
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
                                    + Add Field
                                </button>
                            </div>
                            {formData.fields.map((field, index) => (
                                <div key={index} style={{
                                    padding: '15px',
                                    background: '#f9f9f9',
                                    border: '1px solid #e0e0e0',
                                    borderRadius: '4px',
                                    marginBottom: '10px'
                                }}>
                                    <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '10px' }}>
                                        <strong>Field {index + 1}</strong>
                                        {formData.fields.length > 1 && (
                                            <button
                                                type="button"
                                                onClick={() => removeField(index)}
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
                                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '10px' }}>
                                        <div>
                                            <label style={{ display: 'block', marginBottom: '5px', fontSize: '13px' }}>Field Name</label>
                                            <input
                                                type="text"
                                                value={field.name}
                                                onChange={(e) => updateField(index, 'name', e.target.value)}
                                                required
                                                placeholder="e.g., first_name"
                                                style={{ width: '100%', padding: '6px', border: '1px solid #ddd', borderRadius: '4px' }}
                                            />
                                        </div>
                                        <div>
                                            <label style={{ display: 'block', marginBottom: '5px', fontSize: '13px' }}>Field Type</label>
                                            <select
                                                value={field.type}
                                                onChange={(e) => updateField(index, 'type', e.target.value)}
                                                style={{ width: '100%', padding: '6px', border: '1px solid #ddd', borderRadius: '4px' }}
                                            >
                                                <option value="text">Text</option>
                                                <option value="email">Email</option>
                                                <option value="number">Number</option>
                                                <option value="textarea">Textarea</option>
                                                <option value="select">Dropdown</option>
                                                <option value="radio">Radio</option>
                                                <option value="checkbox">Checkbox</option>
                                                <option value="date">Date</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div style={{ marginTop: '10px' }}>
                                        <label style={{ display: 'block', marginBottom: '5px', fontSize: '13px' }}>Label</label>
                                        <input
                                            type="text"
                                            value={field.label}
                                            onChange={(e) => updateField(index, 'label', e.target.value)}
                                            required
                                            placeholder="Display label for field"
                                            style={{ width: '100%', padding: '6px', border: '1px solid #ddd', borderRadius: '4px' }}
                                        />
                                    </div>
                                    {['select', 'radio', 'checkbox'].includes(field.type) && (
                                        <div style={{ marginTop: '10px' }}>
                                            <label style={{ display: 'block', marginBottom: '5px', fontSize: '13px' }}>
                                                Options (comma-separated)
                                            </label>
                                            <input
                                                type="text"
                                                value={field.options}
                                                onChange={(e) => updateField(index, 'options', e.target.value)}
                                                placeholder="e.g., Option 1, Option 2, Option 3"
                                                style={{ width: '100%', padding: '6px', border: '1px solid #ddd', borderRadius: '4px' }}
                                            />
                                        </div>
                                    )}
                                    <div style={{ marginTop: '10px' }}>
                                        <label style={{ display: 'flex', alignItems: 'center', cursor: 'pointer' }}>
                                            <input
                                                type="checkbox"
                                                checked={field.required}
                                                onChange={(e) => updateField(index, 'required', e.target.checked)}
                                                style={{ marginRight: '8px' }}
                                            />
                                            Required field
                                        </label>
                                    </div>
                                </div>
                            ))}
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
                                {editingId ? 'Update' : 'Create'} Form
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
                {forms.length === 0 ? (
                    <div style={{ padding: '40px', textAlign: 'center', color: '#666' }}>
                        No forms created yet
                    </div>
                ) : (
                    forms.map(form => (
                        <div key={form.id} style={{
                            background: '#fff',
                            border: '1px solid #ddd',
                            borderRadius: '4px',
                            padding: '20px'
                        }}>
                            <h3 style={{ margin: '0 0 10px 0' }}>{form.name}</h3>
                            <div style={{
                                fontSize: '12px',
                                color: '#fff',
                                background: '#666',
                                padding: '4px 8px',
                                borderRadius: '4px',
                                display: 'inline-block',
                                marginBottom: '10px'
                            }}>
                                {form.form_type}
                            </div>
                            <p style={{ color: '#666', fontSize: '13px', marginBottom: '10px' }}>
                                {form.description || 'No description'}
                            </p>
                            <div style={{ fontSize: '12px', color: '#666', marginBottom: '15px' }}>
                                Fields: {form.fields ? (typeof form.fields === 'string' ? JSON.parse(form.fields).length : form.fields.length) : 0}
                            </div>
                            <div style={{ display: 'flex', gap: '10px' }}>
                                <button
                                    onClick={() => handleEdit(form)}
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
                                    onClick={() => handleDelete(form.id)}
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

export default Forms;
