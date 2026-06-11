import Navbar from '@/Components/Navbar';
import Footer from '@/Components/Footer';

export default function Guest({ children }) {
    return (
        <div className="min-h-screen bg-[#f0f0f0] font-sans flex flex-col">
            <Navbar auth={null} />
            <div className="flex flex-col items-center justify-center px-4 py-16 flex-1">
                <div className="w-full max-w-md bg-white rounded-2xl shadow-lg border border-gray-100 px-8 py-8">
                    {children}
                </div>
            </div>
            <Footer />
        </div>
    );
}
