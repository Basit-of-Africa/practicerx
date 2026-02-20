import React from 'react';

const Avatar = ({ src, name = '', size = 40, className = '', alt = '' }) => {
    const initials = name
        ? name
              .split(' ')
              .map((s) => s[0])
              .join('')
              .slice(0, 2)
              .toUpperCase()
        : '';

    const style = {
        width: typeof size === 'number' ? `${size}px` : size,
        height: typeof size === 'number' ? `${size}px` : size,
        borderRadius: '50%',
        display: 'inline-flex',
        alignItems: 'center',
        justifyContent: 'center',
        background: 'var(--color-border)',
        color: '#111827',
        fontWeight: 600,
        fontSize: '14px',
        overflow: 'hidden',
    };

    if (src) {
        return <img src={src} alt={alt || name} className={className} style={style} />;
    }

    return (
        <div className={className} style={style} aria-hidden={!name} title={name}>
            {initials}
        </div>
    );
};

export default Avatar;
