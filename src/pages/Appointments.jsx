import Calendar from '../components/Calendar';
import { Card, Button, Icon, Modal, Form, Input, Select, Textarea } from '../components/ui';
import { useState } from '@wordpress/element';

const Appointments = () => {
    const [open, setOpen] = useState(false);

    const handleSubmit = (formData) => {
        // Minimal demo submission; convert FormData to object
        const obj = {};
        for (let pair of formData.entries()) obj[pair[0]] = pair[1];
        console.log('Booking data', obj);
        setOpen(false);
        alert('Booking created (demo)');
    };
    return (
        <div className="practicerx-page px-portal">
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '16px' }}>
                <div>
                    <h1 className="text-2xl font-semibold">Appointments</h1>
                    <p className="text-sm text-muted">Manage your schedule here.</p>
                </div>
                <div>
                    <Button onClick={() => setOpen(true)} className="mr-2"><Icon name="calendar" />&nbsp; New Booking</Button>
                    <Button variant="ghost" onClick={() => window.location.hash = '#/client/appointments'}>Client View</Button>
                </div>
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: '2fr 1fr', gap: '16px' }}>
                <div>
                    <Card>
                        <Calendar />
                    </Card>
                </div>
                <div>
                    <Card>
                        <h3 className="text-lg font-medium">Upcoming</h3>
                        <ul style={{ marginTop: '12px', listStyle: 'none', padding: 0 }}>
                            <li style={{ padding: '8px 0', borderBottom: '1px solid #f1f1f1' }}>
                                <strong>John Doe</strong><br />
                                <small className="text-muted">Apr 1, 10:00 AM</small>
                            </li>
                            <li style={{ padding: '8px 0', borderBottom: '1px solid #f1f1f1' }}>
                                <strong>Jane Smith</strong><br />
                                <small className="text-muted">Apr 1, 11:00 AM</small>
                            </li>
                        </ul>
                        <div style={{ marginTop: '12px', textAlign: 'center' }}>
                            <Button variant="ghost" onClick={() => alert('View all')}>View All</Button>
                        </div>
                    </Card>
                </div>
            </div>

            <Modal isOpen={open} onClose={() => setOpen(false)} title="New Booking">
                <Form onSubmit={handleSubmit}>
                    <div style={{ display: 'grid', gap: '8px' }}>
                        <label>Client Name</label>
                        <input name="client_name" className="border rounded px-3 py-3 touch-target" />

                        <label>Service</label>
                        <select name="service" className="border rounded px-3 py-3 touch-target">
                            <option value="consult">Consultation</option>
                            <option value="followup">Follow-up</option>
                        </select>

                        <label>Notes</label>
                        <textarea name="notes" className="border rounded px-3 py-3 touch-target" />

                        <div className="modal-actions" style={{ marginTop: '8px' }}>
                            <Button variant="ghost" onClick={() => setOpen(false)} className="btn-block">Cancel</Button>
                            <button type="submit" className="btn-block create-button">Create</button>
                        </div>
                    </div>
                </Form>
            </Modal>
        </div>
    );
};

export default Appointments;
