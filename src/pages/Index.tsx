import Navbar from "@/components/Navbar";
import HeroCarousel from "@/components/HeroCarousel";
import StatsStrip from "@/components/StatsStrip";
import HomeNewsCarousel from "@/components/HomeNewsCarousel";
import WhyChooseUs from "@/components/WhyChooseUs";
import ServicesSection from "@/components/ServicesSection";
import DualFocusSection from "@/components/DualFocusSection";
import Testimonials from "@/components/Testimonials";
import ContactSection from "@/components/ContactSection";
import Footer from "@/components/Footer";

// StatsStrip and ServicesSection render content that was already editable in the
// admin but appeared nowhere on the site. Both hide themselves when their content
// is empty or switched off, so the owner can remove either from the page without a
// developer.
const Index = () => (
  <div className="min-h-screen bg-background">
    <Navbar />
    <HeroCarousel />
    <StatsStrip />
    <WhyChooseUs />
    <ServicesSection />
    <DualFocusSection />
    <HomeNewsCarousel />
    <Testimonials />
    <ContactSection />
    <Footer />
  </div>
);

export default Index;
