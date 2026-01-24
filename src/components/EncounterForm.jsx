import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

const EncounterForm = ({ patientId, onSave }) => {
    const [content, setContent] = useState('');
    const [type, setType] = useState('general');
    const [isSaving, setIsSaving] = useState(false);

    const handleSubmit = (e) => {
        e.preventDefault();
        setIsSaving(true);

        apiFetch({
            path: '/ppms/v1/encounters',
            method: 'POST',
            data: {
                patient_id: patientId,
                practitioner_id: 1, // TODO: Get current user ID
                type,
                content,
            },
        }).then((response) => {
            setContent('');
            if (onSave) onSave(response);
        }).catch((error) => {
            console.error(error);
            alert('Failed to save encounter.');
        }).finally(() => {
            setIsSaving(false);
        });
    };

    return (
        <div className="practicerx-encounter-form" style={{ marginTop: '20px', padding: '15px', background: '#f9f9f9', border: '1px solid #ddd' }}>
            <h4>Add Clinical Note</h4>
            <form onSubmit={handleSubmit}>
                <div className="form-group" style={{ marginBottom: '10px' }}>
                    <label style={{ display: 'block' }}>Type</label>
                    <select value={type} onChange={(e) => setType(e.target.value)} style={{ width: '100%' }}>
                        <option value="general">General Note</option>
                        <option value="soap">SOAP Note</option>
                        <option value="assessment">Assessment</option>
                        <option value="prescription">Prescription</option>
                    </select>
                </div>
                <div className="form-group" style={{ marginBottom: '10px' }}>
                    <label style={{ display: 'block' }}>Content</label>
                    <textarea
                        value={content}
                        onChange={(e) => setContent(e.target.value)}
                        rows="5"
                        required
                        style={{ width: '100%' }}
                    />
                </div>
                <button type="submit" disabled={isSaving} className="button button-primary">
                    {isSaving ? 'Saving...' : 'Save Note'}
                </button>
            </form>
        </div>
    );
};

export default EncounterForm;
