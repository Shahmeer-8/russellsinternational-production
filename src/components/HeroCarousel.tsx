import { useCallback, useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { ArrowRight, ChevronLeft, ChevronRight } from "lucide-react";
import { useHeroSlides, useTickerItems } from "@/hooks/api";
import fallbackHeroImage from "@/assets/hero-students-clean.jpg";

const fallbackSlides = [
  {
    image: fallbackHeroImage,
    eyebrow: "Admissions Open 2026",
    title: "Your Global Career Starts Here",
    desc: "Expert guidance for study abroad, skill training, and career placement trusted by 5,000+ students.",
    cta: { label: "Explore Programs", to: "/skills" },
    secondaryCta: { label: "Free Consultation", to: "/#contact" },
  },
];

const fallbackTickerItems = [
  "Admissions Open for September 2026 Intake",
  "95% Visa Success Rate for UK, Canada & AU",
  "New IT Courses Starting Monthly",
  "NAVTTC Free Training Now Available",
];

const HeroCarousel = () => {
  const [active, setActive] = useState(0);

  const { data: slidesData } = useHeroSlides();
  const { data: tickerData } = useTickerItems();

  const apiSlides = (slidesData?.data ?? [])
    .filter((s) => s.is_active)
    .map((s) => ({
      image: s.image_url,
      eyebrow: s.eyebrow,
      title: s.title,
      desc: s.description,
      cta: { label: s.cta_label, to: s.cta_url },
      secondaryCta: { label: s.secondary_cta_label, to: s.secondary_cta_url },
    }));

  const slides = apiSlides.length > 0 ? apiSlides : fallbackSlides;
  const apiTickerItems = (tickerData?.data ?? []).map((t) => `${t.emoji ?? ""} ${t.text}`.trim());
  const tickerItems = apiTickerItems.length > 0 ? apiTickerItems : fallbackTickerItems;

  const goTo = useCallback(
    (nextIndex: number) => {
      setActive((nextIndex + slides.length) % slides.length);
    },
    [slides.length],
  );

  const go = useCallback(
    (dir: number) => {
      setActive((p) => (p + dir + slides.length) % slides.length);
    },
    [slides.length],
  );

  useEffect(() => {
    if (slides.length <= 1) return;
    const id = window.setTimeout(() => {
      go(1);
    }, 5000);
    return () => window.clearTimeout(id);
  }, [active, go, slides.length]);

  return (
    <section className="relative pt-16">
      {tickerItems.length > 0 && (
        <div className="bg-primary text-primary-foreground py-2.5 overflow-hidden">
          <div className="flex animate-[scroll_20s_linear_infinite] whitespace-nowrap gap-12">
            {[...tickerItems, ...tickerItems].map((t, i) => (
              <span key={`${t}-${i}`} className="text-xs font-medium tracking-wide">{t}</span>
            ))}
          </div>
        </div>
      )}

      <div className="relative h-[560px] md:h-[640px] overflow-hidden bg-primary">
        {slides.map((s, i) => (
          <div
            key={`${s.title}-${i}`}
            className={`absolute inset-0 transition-opacity duration-700 ${i === active ? "opacity-100" : "opacity-0 pointer-events-none"}`}
            aria-hidden={i !== active}
          >
            {s.image && (
              <img
                src={s.image}
                alt={s.title}
                className="w-full h-full object-cover"
                loading={i === 0 ? "eager" : "lazy"}
                decoding="async"
              />
            )}
            <div className="absolute inset-0 bg-gradient-to-r from-primary/90 via-primary/70 to-primary/30" />
            <div className="absolute inset-0 flex items-center">
              <div className="container mx-auto px-4 md:px-8">
                <div className="max-w-2xl text-primary-foreground animate-fade-in" key={`${i}-${active}`}>
                  <span className="inline-flex items-center gap-1.5 bg-accent text-accent-foreground text-xs font-bold uppercase tracking-wider px-3 py-1.5 rounded-full mb-5">
                    {s.eyebrow}
                  </span>
                  <h1 className="text-4xl sm:text-5xl lg:text-6xl font-extrabold leading-[1.1] font-heading mb-5">
                    {s.title}
                  </h1>
                  <p className="text-lg text-primary-foreground/80 max-w-xl leading-relaxed mb-8">
                    {s.desc}
                  </p>
                  <div className="flex flex-wrap gap-4">
                    <Link to={s.cta.to} className="btn-accent inline-flex items-center gap-2 group">
                      {s.cta.label} <ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                    </Link>
                    {s.secondaryCta.label && s.secondaryCta.to && (
                      <Link to={s.secondaryCta.to} className="bg-primary-foreground/10 backdrop-blur-sm border border-primary-foreground/20 text-primary-foreground px-7 py-3.5 rounded-xl font-semibold hover:bg-primary-foreground/20 transition-all">
                        {s.secondaryCta.label}
                      </Link>
                    )}
                  </div>
                </div>
              </div>
            </div>
          </div>
        ))}

        {slides.length > 1 && (
          <>
            <button
              onClick={() => go(-1)}
              className="absolute left-4 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-background/20 hover:bg-background/40 backdrop-blur-md flex items-center justify-center text-primary-foreground transition-colors"
              aria-label="Previous slide"
            >
              <ChevronLeft className="w-5 h-5" />
            </button>
            <button
              onClick={() => go(1)}
              className="absolute right-4 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-background/20 hover:bg-background/40 backdrop-blur-md flex items-center justify-center text-primary-foreground transition-colors"
              aria-label="Next slide"
            >
              <ChevronRight className="w-5 h-5" />
            </button>

            <div className="absolute bottom-6 left-1/2 -translate-x-1/2 flex gap-2">
              {slides.map((_, i) => (
                <button
                  key={i}
                  onClick={() => goTo(i)}
                  className={`h-2 rounded-full transition-all ${i === active ? "w-8 bg-accent" : "w-2 bg-primary-foreground/40 hover:bg-primary-foreground/60"}`}
                  aria-label={`Go to slide ${i + 1}`}
                />
              ))}
            </div>
          </>
        )}
      </div>
    </section>
  );
};

export default HeroCarousel;
