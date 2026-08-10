import { useEffect, useMemo, useRef, useState } from "react";
import { ArrowRight, Award, Clock } from "lucide-react";
import { useScrollReveal } from "@/hooks/useScrollReveal";
import DetailDrawer from "@/components/DetailDrawer";
import { useLanguageSections } from "@/hooks/api";
import { useSectionCopy } from "@/hooks/useSectionCopy";
import { resolveIcon } from "@/lib/icons";
import type { LanguageProgram, LanguageSection } from "@/types/api";

/** Splits a stored "bg-… text-…" pair, falling back to the section's own pair. */
function splitColor(colorClass: string | null, fallback: string): [string, string] {
  const [bg, fg] = (colorClass || fallback).split(" ");
  const [fallbackBg, fallbackFg] = fallback.split(" ");

  return [bg || fallbackBg, fg || fallbackFg];
}

const LanguagesSection = () => {
  const { ref, visible } = useScrollReveal();
  const copy = useSectionCopy("languages", "intro");
  const { data, isLoading } = useLanguageSections();
  const [activeSlug, setActiveSlug] = useState<string | null>(null);
  const [drawerOpen, setDrawerOpen] = useState(false);
  const [selected, setSelected] = useState<LanguageProgram | null>(null);
  const tabRefs = useRef<Record<string, HTMLButtonElement | null>>({});

  const sections = useMemo<LanguageSection[]>(() => data?.data ?? [], [data?.data]);
  const active = sections.find((s) => s.slug === activeSlug) ?? sections[0];

  // Keep the selected tab in view as the strip scrolls horizontally. The method
  // is called optionally: it is absent in jsdom and in some older webviews, and
  // an unguarded call there would throw and take the page down.
  useEffect(() => {
    if (!active) return;
    tabRefs.current[active.slug]?.scrollIntoView?.({ block: "nearest", inline: "center", behavior: "smooth" });
  }, [active]);

  if (isLoading) {
    return (
      <section className="py-20 md:py-28">
        <div className="container mx-auto px-4 md:px-8">
          <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {[...Array(3)].map((_, i) => (
              <div key={i} className="premium-card p-6 h-64 animate-pulse" />
            ))}
          </div>
        </div>
      </section>
    );
  }

  // Nothing configured: render nothing rather than headings with no content.
  if (!active) return null;

  const SectionIcon = resolveIcon(active.icon_name);
  const [sectionBg, sectionFg] = splitColor(active.color_class, "bg-blue-50 text-blue-600");

  return (
    <>
      <section className="py-20 md:py-28">
        <div
          ref={ref}
          className={`container mx-auto px-4 md:px-8 transition-all duration-700 ${visible ? "opacity-100" : "opacity-0"}`}
        >
          <div className="text-center mb-10">
            <span className="section-label">{copy("eyebrow", "Language Programs")}</span>
            <h2 className="section-title mt-3">{copy("title", "Speak the World")}</h2>
            <p className="text-muted-foreground mt-4 max-w-2xl mx-auto">
              {copy("subtitle", "Exam-focused language training for study abroad, visa pathways, work routes and global careers.")}
            </p>
          </div>

          {/* One row however many sections exist, so adding a language never adds
              vertical height on mobile. Swipes horizontally; scrollbar hidden. */}
          <div className="mb-10 -mx-4 px-4 md:mx-0 md:px-0">
            <div
              role="tablist"
              aria-label="Language sections"
              className="flex gap-1.5 overflow-x-auto snap-x snap-mandatory bg-muted rounded-2xl p-1.5 md:justify-center [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
            >
              {sections.map((section) => {
                const Icon = resolveIcon(section.icon_name);
                const isActive = section.slug === active.slug;

                return (
                  <button
                    key={section.slug}
                    ref={(el) => {
                      tabRefs.current[section.slug] = el;
                    }}
                    type="button"
                    role="tab"
                    aria-selected={isActive}
                    onClick={() => setActiveSlug(section.slug)}
                    className={`min-h-12 shrink-0 snap-start rounded-xl px-4 sm:px-5 text-xs sm:text-sm font-semibold transition-all duration-300 flex items-center justify-center gap-2 ${
                      isActive ? "bg-background text-foreground shadow-md" : "text-muted-foreground hover:text-foreground"
                    }`}
                  >
                    <Icon className="w-4 h-4 shrink-0" />
                    <span className="hidden sm:inline">{section.label}</span>
                    <span className="sm:hidden">{section.tab_label}</span>
                  </button>
                );
              })}
            </div>
          </div>

          <div className="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
              <div className="inline-flex items-center gap-2 text-sm font-semibold text-accent mb-2">
                <SectionIcon className="w-4 h-4" />
                {active.tab_label}
              </div>
              <h3 className="font-heading text-2xl md:text-3xl font-extrabold text-foreground">{active.heading}</h3>
              {active.subtitle && <p className="text-muted-foreground mt-2 max-w-2xl">{active.subtitle}</p>}
            </div>
            <div className="text-sm font-semibold text-muted-foreground">
              {active.programs.length} {active.programs.length === 1 ? "program" : "programs"}
            </div>
          </div>

          <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {active.programs.map((program) => {
              const CardIcon = resolveIcon(program.icon_name, SectionIcon);
              const [cardBg, cardFg] = splitColor(program.color_class, active.color_class);

              return (
                <div
                  key={program.id}
                  className="premium-card p-6 group cursor-pointer"
                  onClick={() => {
                    setSelected(program);
                    setDrawerOpen(true);
                  }}
                >
                  <div className="flex items-start justify-between mb-5 gap-4">
                    <div className={`w-12 h-12 rounded-xl ${cardBg} flex items-center justify-center group-hover:scale-110 transition-transform duration-300`}>
                      <CardIcon className={`w-6 h-6 ${cardFg}`} />
                    </div>
                    {program.badge && (
                      <span className="text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full bg-accent/10 text-accent text-right">
                        {program.badge}
                      </span>
                    )}
                  </div>
                  <h4 className="font-bold text-foreground font-heading text-lg mb-2 group-hover:text-accent transition-colors">
                    {program.title}
                  </h4>
                  <div className="flex items-center gap-3 text-xs text-muted-foreground mb-4">
                    <span className="flex items-center gap-1"><Clock className="w-3.5 h-3.5" /> {program.duration}</span>
                  </div>
                  <p className="text-sm text-muted-foreground leading-relaxed mb-5">{program.description}</p>
                  <span className="inline-flex items-center gap-1.5 text-sm font-semibold text-accent group-hover:gap-2.5 transition-all">
                    Learn More <ArrowRight className="w-3.5 h-3.5" />
                  </span>
                </div>
              );
            })}
          </div>
        </div>
      </section>

      <DetailDrawer open={drawerOpen} onClose={() => setDrawerOpen(false)} title={selected?.title || "Language Program"}>
        {selected && (() => {
          const DrawerIcon = resolveIcon(selected.icon_name, SectionIcon);
          const [drawerBg, drawerFg] = splitColor(selected.color_class, active.color_class);

          return (
            <div className="space-y-6">
              <div className={`w-16 h-16 rounded-2xl ${drawerBg} flex items-center justify-center`}>
                <DrawerIcon className={`w-8 h-8 ${drawerFg}`} />
              </div>
              <div>
                <h4 className="font-heading font-bold text-xl text-foreground mb-2">{selected.title}</h4>
                <p className="text-muted-foreground leading-relaxed">{selected.description}</p>
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div className="bg-muted/50 rounded-xl p-4">
                  <div className="text-xs text-muted-foreground mb-1">Duration</div>
                  <div className="font-semibold text-foreground text-sm">{selected.duration}</div>
                </div>
                <div className="bg-muted/50 rounded-xl p-4">
                  <div className="text-xs text-muted-foreground mb-1">Certification</div>
                  <div className="font-semibold text-foreground text-sm flex items-center gap-1.5">
                    <Award className="w-4 h-4 text-accent" /> {selected.badge}
                  </div>
                </div>
              </div>
              {selected.benefits.length > 0 && (
                <div>
                  <h5 className="font-semibold text-foreground mb-3">What's Included</h5>
                  <ul className="space-y-2">
                    {selected.benefits.map((benefit) => (
                      <li key={benefit} className="flex items-start gap-2 text-sm text-muted-foreground">
                        <div className="w-1.5 h-1.5 rounded-full bg-accent mt-1.5 shrink-0" />
                        {benefit}
                      </li>
                    ))}
                  </ul>
                </div>
              )}
            </div>
          );
        })()}
      </DetailDrawer>
    </>
  );
};

export default LanguagesSection;
