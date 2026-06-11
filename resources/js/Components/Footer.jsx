export default function Footer() {
    const year = new Date().getFullYear();
    return (
        <footer style={{ backgroundColor: '#1a1a2e', color: '#a0a0b0', fontSize: '0.78rem', padding: '10px 0', textAlign: 'center', width: '100%', marginTop: 'auto' }}>
            <span>
                &copy; {year} Todos los derechos reservados &mdash; Desarrollado por{' '}
                <strong style={{ color: '#ffffff' }}>JaloDev</strong>{' '}
                &mdash;{' '}
                <a href="mailto:servicio@kingol.jalodev.com" style={{ color: '#a0a0b0', textDecoration: 'none' }}>
                    servicio@kingol.jalodev.com
                </a>
            </span>
        </footer>
    );
}
