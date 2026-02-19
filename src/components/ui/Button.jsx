import React from 'react';

const Button = ({ children, variant = 'primary', className = '', ...props }) => {
    const base = 'inline-flex items-center justify-center px-4 py-2 rounded';
    const variants = {
        primary: 'bg-primary text-white hover:opacity-95',
        ghost: 'bg-transparent text-primary border border-gray-200',
    };

    return (
        <button className={`${base} ${variants[variant] || variants.primary} ${className}`} {...props}>
            {children}
        </button>
    );
};

export default Button;
