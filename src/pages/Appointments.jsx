import Calendar from '../components/Calendar';
import { Card, Button, Icon, Modal, Form, Input, Select, Textarea, Label, useToast } from '../components/ui';
import { useState } from '@wordpress/element';

const Appointments = () => {
    const [open, setOpen] = useState(false);
    const [clientName, setClientName] = useState('');
    const [service, setService] = useState('consult');
    const [notes, setNotes] = useState('');

    const { addToast } = useToast();
    const [loading, setLoading] = useState(false);
    const [formError, setFormError] = useState('');

    const handleSubmit = async (data) => {
        setFormError('');

        // Basic client-side validation
        if (!data.client_name || !data.client_name.trim()) {
            setFormError('Client name is required');
            return;
        }

        setLoading(true);
        try {
            const res = await fetch(practicerxSettings.root + 'appointments', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': practicerxSettings.nonce,
                },
                body: JSON.stringify(data),
            });

            const result = await res.json();
            if (!res.ok) {
                const msg = (result && result.message) || 'Failed to create appointment';
                addToast({ title: 'Error', description: msg, variant: 'default' });
                setFormError(msg);
                setLoading(false);
                return;
            }

            addToast({ title: 'Booked', description: 'Appointment created', variant: 'primary' });
            // reset fields
            setClientName('');
            setService('consult');
            setNotes('');
            setOpen(false);
        } catch (err) {
            setFormError('Network error');
            addToast({ title: 'Network error', description: String(err), variant: 'default' });
        } finally {
            setLoading(false);
        }
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
                            <Label htmlFor="ap-client-name">Client Name</Label>
                            <Input id="ap-client-name" name="client_name" className="touch-target" value={clientName} onChange={(e) => setClientName(e.target.value)} />

                            <Label htmlFor="ap-service">Service</Label>
                            <Select id="ap-service" name="service" className="touch-target" value={service} onChange={(e) => setService(e.target.value)} options={[{ value: 'consult', label: 'Consultation' }, { value: 'followup', label: 'Follow-up' }]} />

                            <Label htmlFor="ap-notes">Notes</Label>
                            <Textarea id="ap-notes" name="notes" className="touch-target" value={notes} onChange={(e) => setNotes(e.target.value)} />

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
