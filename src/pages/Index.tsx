import Navbar from "@/components/Navbar";
import HeroCarousel from "@/components/HeroCarousel";
import HomeNewsCarousel from "@/components/HomeNewsCarousel";
import WhyChooseUs from "@/components/WhyChooseUs";
import DualFocusSection from "@/components/DualFocusSection";
import Testimonials from "@/components/Testimonials";
import ContactSection from "@/components/ContactSection";
import Footer from "@/components/Footer";

const Index = () => (
  <div className="min-h-screen bg-background">
    <Navbar />
    <HeroCarousel />
    <WhyChooseUs />
    <DualFocusSection />
    <HomeNewsCarousel />
    <Testimonials />
    <ContactSection />
    <Footer />
  </div>
);

export default Index;
