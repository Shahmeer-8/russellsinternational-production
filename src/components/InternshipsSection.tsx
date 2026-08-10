import { useState } from "react";
import { useScrollReveal } from "@/hooks/useScrollReveal";
import DetailDrawer from "@/components/DetailDrawer";
import { Rocket, Clock, MapPin, ArrowRight, BadgeCheck } from "lucide-react";
import { useInternships } from "@/hooks/api";
import ResponsiveCardRow from "@/components/ResponsiveCardRow";
import { useSectionCopy } from "@/hooks/useSectionCopy";

type InternshipCard = {
  title: string;
  company: string;
  location: string;
  duration: string;
  type: string;
  desc: string;
  skills: string[];
  gains?: string[];
  image: string | null;
};

const InternshipsSection = () => {
  const copy = useSectionCopy("careers", "internships");
  const { ref, visible } = useScrollReveal();
  const [drawerOpen, setDrawerOpen] = useState(false);
  const [selected, setSelected] = useState<InternshipCard | null>(null);

  const { data: internshipsData, isLoading } = useInternships();
  const apiInternships = (internshipsData?.data?.data ?? []).map((i) => ({ title: i.title, company: i.company, location: i.location, duration: i.duration, type: i.type, desc: i.description, skills: i.skills ?? [], gains: i.gains ?? [], image: i.image_url }));
  const internshipsList = apiInternships;
  const introImage = internshipsList.find((item) => item.image)?.image;

  const openDrawer = (item: InternshipCard) => { setSelected(item); setDrawerOpen(true); };

  return (
    <>
      <section id="internships" className="py-20 md:py-28">
        <div
          ref={ref}
          className={`container mx-auto px-4 md:px-8 transition-all duration-700 ${visible ? "opacity-100" : "opacity-0"}`}
        >
          <div className="grid lg:grid-cols-2 gap-12 items-center mb-14">
            <div>
              <span className="section-label">{copy("eyebrow", "Internships")}</span>
              <h2 className="section-title mt-3 mb-5">{copy("title", "Gain Real-World Experience")}</h2>
              <p className="text-muted-foreground leading-relaxed">
                {copy("subtitle", "Bridge the gap between learning and working. Our internship programs place you in real projects with industry mentors, giving you hands-on experience that employers value.")}
              </p>
            </div>
            {introImage && (
              <div className="rounded-3xl overflow-hidden shadow-xl">
                <img src={introImage} alt="" className="w-full h-auto object-cover" loading="lazy" decoding="async" width={960} height={640} />
              </div>
            )}
          </div>

          {isLoading ? (
            <div className="grid sm:grid-cols-2 gap-6">
              {[...Array(2)].map((_, i) => <div key={i} className="premium-card h-64 animate-pulse" />)}
            </div>
          ) : internshipsList.length === 0 ? null : (
            <ResponsiveCardRow
              gridClassName="grid sm:grid-cols-2 gap-6"
              items={internshipsList.map((item) => ({
                key: item.title,
                node: (
              <div className="premium-card p-6 group cursor-pointer h-full" onClick={() => openDrawer(item)}>
                <div className="flex items-start justify-between mb-4">
                  <div className="w-10 h-10 rounded-xl bg-accent/10 flex items-center justify-center">
                    <Rocket className="w-5 h-5 text-accent" />
                  </div>
                  <span className={`text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full ${item.type === "Paid" ? "bg-green-50 text-green-700" : "bg-blue-50 text-blue-700"}`}>
                    {item.type}
                  </span>
                </div>
                <h3 className="font-bold text-foreground font-heading text-base mb-1 group-hover:text-accent transition-colors">{item.title}</h3>
                <p className="text-xs text-muted-foreground mb-3">{item.company}</p>
                <div className="flex flex-wrap gap-3 text-xs text-muted-foreground mb-4">
                  <span className="flex items-center gap-1"><MapPin className="w-3 h-3" /> {item.location}</span>
                  <span className="flex items-center gap-1"><Clock className="w-3 h-3" /> {item.duration}</span>
                </div>
                <div className="flex flex-wrap gap-1.5 mb-4">
                  {item.skills.map((s) => (
                    <span key={s} className="text-[10px] bg-muted px-2 py-0.5 rounded-full text-muted-foreground font-medium">{s}</span>
                  ))}
                </div>
                <span className="inline-flex items-center gap-1.5 text-sm font-semibold text-accent group-hover:gap-2.5 transition-all">
                  View Details <ArrowRight className="w-3.5 h-3.5" />
                </span>
              </div>
                ),
              }))}
            />
          )}
        </div>
      </section>

      <DetailDrawer open={drawerOpen} onClose={() => setDrawerOpen(false)} title={selected?.title || "Internship Details"}>
        {selected && (
          <div className="space-y-6">
            <div className="w-16 h-16 rounded-2xl bg-accent/10 flex items-center justify-center">
              <Rocket className="w-8 h-8 text-accent" />
            </div>
            <div>
              <h4 className="font-heading font-bold text-xl text-foreground">{selected.title}</h4>
              <p className="text-sm text-muted-foreground mt-1">{selected.company}</p>
            </div>
            <p className="text-muted-foreground leading-relaxed">{selected.desc}</p>
            <div className="grid grid-cols-2 gap-4">
              {[
                { label: "Location", value: selected.location },
                { label: "Duration", value: selected.duration },
                { label: "Type", value: selected.type },
                { label: "Skills", value: selected.skills.join(", ") },
              ].map((item) => (
                <div key={item.label} className="bg-muted/50 rounded-xl p-4">
                  <div className="text-xs text-muted-foreground mb-1">{item.label}</div>
                  <div className="font-semibold text-foreground text-sm">{item.value}</div>
                </div>
              ))}
            </div>
            <div>
              <h5 className="font-semibold text-foreground mb-3">What You'll Gain</h5>
              <ul className="space-y-2">
                {(selected.gains ?? []).map((item: string) => (
                  <li key={item} className="flex items-start gap-2 text-sm text-muted-foreground">
                    <BadgeCheck className="w-4 h-4 text-accent shrink-0 mt-0.5" />
                    {item}
                  </li>
                ))}
              </ul>
            </div>
          </div>
        )}
      </DetailDrawer>
    </>
  );
};

export default InternshipsSection;
