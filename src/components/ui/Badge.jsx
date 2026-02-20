import React from 'react';

const Badge = ({ children, variant = 'default', className = '' }) => {
    const base = {
        display: 'inline-block',
        padding: '2px 8px',
        borderRadius: '999px',
        fontSize: '12px',
        lineHeight: '18px',
    };

    const variants = {
        default: {
            background: 'var(--color-surface)',
            color: 'var(--color-muted)',
            border: '1px solid var(--color-border)'
        },
        primary: {
            background: 'var(--color-primary)',
            color: '#fff'
        }
    };

    const style = Object.assign({}, base, variants[variant] || variants.default);

    return (
        <span className={className} style={style} role="status">
            {children}
        </span>
    );
};

export default Badge;
