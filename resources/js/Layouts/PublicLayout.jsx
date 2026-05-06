import Navbar from '@/Components/Navbar';

export default function PublicLayout({ auth, children }) {
    return (
        <div className="min-h-screen bg-[#f0f0f0] font-sans">
            <Navbar auth={auth} />
            <main>{children}</main>
        </div>
    );
}
