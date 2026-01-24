import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

const EncounterForm = ({ patientId, onSave }) => {
    const [content, setContent] = useState('');
    const [type, setType] = useState('general');
    const [isSaving, setIsSaving] = useState(false);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setIsSaving(true);

        try {
            const response = await apiFetch({
                path: '/ppms/v1/encounters',
                method: 'POST',
                data: {
                    patient_id: patientId,
                    practitioner_id: 1, // TODO: Get current user ID
                    type,
                    content,
                },
            });

            setContent('');
            if (onSave) {
                onSave(response);
            }
        } catch (error) {
            console.error(error);
            alert('Failed to save encounter.');
        } finally {
            setIsSaving(false);
        }
    };

    return (
        <div className="practicerx-encounter-form">
            <h4>Add Clinical Note</h4>
            <form onSubmit={handleSubmit}>
                <div className="form-group">
                    <label>Type</label>
                    <select value={type} onChange={(e) => setType(e.target.value)}>
                        <option value="general">General Note</option>
                        <option value="soap">SOAP Note</option>
                        <option value="assessment">Assessment</option>
                        <option value="prescription">Prescription</option>
                    </select>
                </div>
                <div className="form-group">
                    <label>Content</label>
                    <textarea
                        value={content}
                        onChange={(e) => setContent(e.target.value)}
                        rows="5"
                        required
                    />
                </div>
                <button type="submit" disabled={isSaving}>
                    {isSaving ? 'Saving...' : 'Save Note'}
                </button>
            </form>
        </div>
    );
};

export default EncounterForm;
