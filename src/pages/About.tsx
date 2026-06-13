import Navbar from "@/components/Navbar";
import Footer from "@/components/Footer";
import DynamicPageHero from "@/components/DynamicPageHero";
import CTASection from "@/components/CTASection";
import { useScrollReveal } from "@/hooks/useScrollReveal";
import { usePageSections, useTeamMembers } from "@/hooks/api";
import { sectionImage, sectionText } from "@/lib/content";
import { Target, Eye, Heart, Users } from "lucide-react";
import aboutHeroImg from "@/assets/about-hero.jpg";
import campusLifeImg from "@/assets/campus-life.jpg";
import founderPortraitImg from "@/assets/founder-portrait.jpg";

const pillarIcons = [Target, Eye, Heart];

const fallbackCampus = {
  eyebrow: "Campus Life",
  title: "A Living, Learning Ecosystem",
  body: "A modern learning environment with training labs, counselling spaces, and student support facilities.",
  image: campusLifeImg,
  ctaLabel: "Contact Us",
  ctaUrl: "/#contact",
};

const fallbackFounder = {
  eyebrow: "Founder Message",
  title: "Dear Students, Parents and Well-Wishers",
  body: "Together, we shape brighter futures through education, skills, and global opportunity.",
  image: founderPortraitImg,
};

const fallbackFoundationItems = {
  Mission: "To deliver skill-based programs that prepare students for global success.",
  Vision: "To create a learning climate where students become productive and socially conscious.",
  "Core Values": "Commitment, accessibility, and excellence in every learning journey.",
};

const About = () => {
  const intro = useScrollReveal();
  const founder = useScrollReveal();
  const pillarsR = useScrollReveal();
  const { data: sectionData, isLoading: sectionsLoading } = usePageSections("about");
  const { data: teamData, isLoading: teamLoading } = useTeamMembers();
  const sections = sectionData?.data ?? {};
  const campus = sections.campus_life;
  const founderSection = sections.founder_message;
  const foundation = sections.foundation;
  const foundationItems = foundation?.items && !Array.isArray(foundation.items) ? foundation.items : fallbackFoundationItems;
  const pillars = Object.entries(foundationItems).map(([title, body], index) => ({
    icon: pillarIcons[index % pillarIcons.length],
    title,
    body: String(body),
  }));
  const displayedTeam = (teamData?.data ?? []).map((member) => ({
    name: member.name,
    role: member.role,
    note: member.bio,
  }));

  return (
    <div className="min-h-screen bg-background">
      <Navbar />
      <DynamicPageHero
        page="about"
        fallback={{
          eyebrow: "About Us",
          title: "Change Begins With One Dream",
          description: "A premier education consultancy and IT training institute bridging ambition with global opportunity.",
          image: aboutHeroImg,
          crumbs: [{ label: "Home", to: "/" }, { label: "About Us" }],
        }}
      />

      {sectionsLoading ? (
        <section className="py-20 md:py-28" aria-hidden="true">
          <div className="container mx-auto px-4 md:px-8">
            <div className="h-80 rounded-3xl bg-muted animate-pulse" />
          </div>
        </section>
      ) : (
        <section className="py-20 md:py-28">
          <div
            ref={intro.ref}
            className={`container mx-auto px-4 md:px-8 transition-all duration-700 ${intro.visible ? "opacity-100" : "opacity-0"}`}
          >
            <div className="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
              <div>
                <span className="section-label">{sectionText(campus, "eyebrow", fallbackCampus.eyebrow)}</span>
                <h2 className="section-title mt-3 mb-5">{sectionText(campus, "title", fallbackCampus.title)}</h2>
                <p className="text-muted-foreground leading-relaxed mb-4">
                  {sectionText(campus, "body", fallbackCampus.body)}
                </p>
                {sectionText(campus, "cta_url", fallbackCampus.ctaUrl) && sectionText(campus, "cta_label", fallbackCampus.ctaLabel) && (
                  <a href={sectionText(campus, "cta_url", fallbackCampus.ctaUrl)} className="btn-accent inline-flex">
                    {sectionText(campus, "cta_label", fallbackCampus.ctaLabel)}
                  </a>
                )}
              </div>
              {sectionImage(campus, fallbackCampus.image) && (
                <div className="relative">
                  <div className="rounded-3xl overflow-hidden shadow-xl">
                    <img src={sectionImage(campus, fallbackCampus.image)} alt={sectionText(campus, "title", fallbackCampus.title)} loading="lazy" decoding="async" width={1200} height={800} className="w-full h-auto object-cover" />
                  </div>
                </div>
              )}
            </div>
          </div>
        </section>
      )}

      <section className="py-20 md:py-28 bg-section-alt">
          <div
            ref={founder.ref}
            className={`container mx-auto px-4 md:px-8 transition-all duration-700 ${founder.visible ? "opacity-100" : "opacity-0"}`}
          >
            <div className="grid lg:grid-cols-5 gap-12 items-center">
              <div className="lg:col-span-2">
                {sectionImage(founderSection, fallbackFounder.image) && (
                  <div className="rounded-3xl overflow-hidden shadow-xl max-w-sm mx-auto">
                    <img src={sectionImage(founderSection, fallbackFounder.image)} alt={sectionText(founderSection, "title", fallbackFounder.title)} loading="lazy" decoding="async" width={800} height={1000} className="w-full h-auto object-cover" />
                  </div>
                )}
              </div>
              <div className="lg:col-span-3">
                <span className="section-label">{sectionText(founderSection, "eyebrow", fallbackFounder.eyebrow)}</span>
                <h2 className="section-title mt-3 mb-5">{sectionText(founderSection, "title", fallbackFounder.title)}</h2>
                <p className="text-muted-foreground leading-relaxed mb-4">
                  {sectionText(founderSection, "body", fallbackFounder.body)}
                </p>
              </div>
            </div>
          </div>
        </section>

      {pillars.length > 0 && (
        <section className="py-20 md:py-28">
          <div
            ref={pillarsR.ref}
            className={`container mx-auto px-4 md:px-8 transition-all duration-700 ${pillarsR.visible ? "opacity-100" : "opacity-0"}`}
          >
            <div className="text-center mb-14">
              <span className="section-label">{sectionText(foundation, "eyebrow", "What Drives Us")}</span>
              <h2 className="section-title mt-3">{sectionText(foundation, "title", "Our Foundation")}</h2>
            </div>
            <div className="grid md:grid-cols-3 gap-6">
              {pillars.map((p) => (
                <div key={p.title} className="premium-card p-8 text-center group">
                  <div className="w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center mx-auto mb-5 group-hover:bg-accent group-hover:scale-110 transition-all duration-300">
                    <p.icon className="w-6 h-6 text-primary group-hover:text-accent-foreground transition-colors" />
                  </div>
                  <h3 className="font-heading font-bold text-xl text-foreground mb-3">{p.title}</h3>
                  <p className="text-sm text-muted-foreground leading-relaxed">{p.body}</p>
                </div>
              ))}
            </div>
          </div>
        </section>
      )}

      {teamLoading ? (
        <section className="py-20 md:py-28 bg-section-alt" aria-hidden="true">
          <div className="container mx-auto px-4 md:px-8">
            <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
              {[...Array(3)].map((_, i) => <div key={i} className="premium-card h-40 animate-pulse" />)}
            </div>
          </div>
        </section>
      ) : displayedTeam.length > 0 && (
        <section className="py-20 md:py-28 bg-section-alt">
          <div className="container mx-auto px-4 md:px-8">
            <div className="text-center mb-14">
              <span className="section-label">Our People</span>
              <h2 className="section-title mt-3">Core Team & Advisory Board</h2>
            </div>
            <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
              {displayedTeam.map((m) => (
                <div key={m.name} className="premium-card p-6 flex items-start gap-4">
                  <div className="w-14 h-14 rounded-2xl bg-accent/10 flex items-center justify-center shrink-0">
                    <Users className="w-6 h-6 text-accent" />
                  </div>
                  <div>
                    <div className="font-heading font-bold text-foreground">{m.name}</div>
                    <div className="text-xs text-accent font-semibold uppercase tracking-wider mb-1.5">{m.role}</div>
                    <div className="text-sm text-muted-foreground leading-relaxed">{m.note}</div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </section>
      )}

      <CTASection />
      <Footer />
    </div>
  );
};

export default About;
