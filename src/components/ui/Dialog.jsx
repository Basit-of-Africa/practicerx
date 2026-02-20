import React from 'react';
import Modal from './Modal';
import { Button } from './index';

const Dialog = ({ isOpen, onClose, onConfirm, title, children, confirmLabel = 'Confirm', cancelLabel = 'Cancel', loading = false }) => {
    return (
        <Modal open={isOpen} onClose={onClose} title={title}>
            <div>{children}</div>
            <div className="modal-actions" style={{ marginTop: 16 }}>
                <Button variant="ghost" onClick={onClose}>{cancelLabel}</Button>
                <Button onClick={onConfirm} disabled={loading} className="ml-2">{loading ? 'Working...' : confirmLabel}</Button>
            </div>
        </Modal>
    );
};

export default Dialog;
