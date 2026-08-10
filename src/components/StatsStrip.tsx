import { useScrollReveal } from "@/hooks/useScrollReveal";
import { useStats } from "@/hooks/api";
import { resolveIcon } from "@/lib/icons";
import { Award } from "lucide-react";

/**
 * The stats were editable in the admin but rendered nowhere, so the owner could
 * curate "5,000+ Students Placed" and never see it on the site.
 *
 * Deliberately a compact strip rather than cards: it earns its place near the top
 * of the page without adding meaningful scroll height — two rows on a phone, one
 * on desktop.
 */
const StatsStrip = () => {
  const { ref, visible } = useScrollReveal();
  const { data, isLoading } = useStats();

  const stats = data?.data ?? [];

  if (isLoading || stats.length === 0) {
    return null;
  }

  return (
    <section className="py-10 md:py-14 bg-section-alt">
      <div
        ref={ref}
        className={`container mx-auto px-4 md:px-8 transition-all duration-700 ${visible ? "opacity-100" : "opacity-0"}`}
      >
        <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
          {stats.map((stat) => {
            const Icon = resolveIcon(stat.icon_name, Award);

            return (
              <div key={stat.id} className="flex flex-col items-center text-center gap-2">
                <div className="w-11 h-11 rounded-xl bg-accent/10 flex items-center justify-center">
                  <Icon className="w-5 h-5 text-accent" />
                </div>
                <div className="font-heading text-2xl md:text-3xl font-extrabold text-foreground">{stat.value}</div>
                <div className="text-xs md:text-sm text-muted-foreground leading-snug">{stat.label}</div>
              </div>
            );
          })}
        </div>
      </div>
    </section>
  );
};

export default StatsStrip;
