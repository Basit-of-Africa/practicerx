import React from 'react';
import { Label, Input } from './index';

const Field = ({ id, label, children, className = '', help = '', required = false }) => {
    // If children provided, render them (controlled Input/Select/Textarea), otherwise render a default Input
    return (
        <div className={`prx-field ${className}`} style={{ marginBottom: '12px' }}>
            {label && <Label htmlFor={id}>{label}{required ? ' *' : ''}</Label>}
            {children ? (
                children
            ) : (
                <Input id={id} name={id} />
            )}
            {help && <div className="text-muted" style={{ fontSize: '13px', marginTop: '6px' }}>{help}</div>}
        </div>
    );
};

export default Field;
