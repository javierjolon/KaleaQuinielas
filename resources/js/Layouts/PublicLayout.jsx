import Navbar from '@/Components/Navbar';
import Footer from '@/Components/Footer';

export default function PublicLayout({ auth, children }) {
    return (
        <div className="min-h-screen bg-[#f0f0f0] font-sans flex flex-col">
            <Navbar auth={auth} />
            <main className="flex-1">{children}</main>
            <Footer />
        </div>
    );
}
