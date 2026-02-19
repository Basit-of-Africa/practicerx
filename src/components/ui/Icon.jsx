import React from 'react';

const Icon = ({ name, size = 20, className = '', ...props }) => {
    const common = { width: size, height: size, viewBox: '0 0 24 24', fill: 'none', xmlns: 'http://www.w3.org/2000/svg' };

    const icons = {
        calendar: (
            <svg {...common} className={className} {...props}>
                <rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" strokeWidth="1.5" />
                <path d="M16 3v4M8 3v4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
            </svg>
        ),
        user: (
            <svg {...common} className={className} {...props}>
                <path d="M12 12c2.761 0 5-2.239 5-5s-2.239-5-5-5-5 2.239-5 5 2.239 5 5 5z" stroke="currentColor" strokeWidth="1.5" fill="none" />
                <path d="M3 21c0-4 4-7 9-7s9 3 9 7" stroke="currentColor" strokeWidth="1.5" fill="none" />
            </svg>
        ),
        check: (
            <svg {...common} className={className} {...props}>
                <path d="M20 6L9 17l-5-5" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" fill="none" />
            </svg>
        ),
        x: (
            <svg {...common} className={className} {...props}>
                <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" fill="none" />
            </svg>
        ),
    };

    return icons[name] || null;
};

export default Icon;
