import { useState } from "react";
import { useScrollReveal } from "@/hooks/useScrollReveal";
import DetailDrawer from "@/components/DetailDrawer";
import { Briefcase, MapPin, DollarSign, ArrowRight, Building2 } from "lucide-react";
import { useJobs } from "@/hooks/api";
import { useSectionCopy } from "@/hooks/useSectionCopy";

type JobCard = {
  title: string;
  company: string;
  location: string;
  type: string;
  salary: string;
  desc: string;
  requirements: string[];
};

const JobsSection = () => {
  const copy = useSectionCopy("careers", "jobs");
  const { ref, visible } = useScrollReveal();
  const [drawerOpen, setDrawerOpen] = useState(false);
  const [selected, setSelected] = useState<JobCard | null>(null);

  const { data: jobsData, isLoading } = useJobs();
  const apiJobs = (jobsData?.data?.data ?? []).map((j) => ({ title: j.title, company: j.company, location: j.location, type: j.type, salary: j.salary ?? '', desc: j.description, requirements: j.requirements ?? [] }));
  const jobsList = apiJobs;

  const openDrawer = (job: JobCard) => { setSelected(job); setDrawerOpen(true); };

  return (
    <>
      <section id="jobs" className="py-20 md:py-28 bg-section-alt">
        <div
          ref={ref}
          className={`container mx-auto px-4 md:px-8 transition-all duration-700 ${visible ? "opacity-100" : "opacity-0"}`}
        >
          <div className="text-center mb-14">
            <span className="section-label">{copy("eyebrow", "Career Opportunities")}</span>
            <h2 className="section-title mt-3">{copy("title", "Join Our Team or Our Partners")}</h2>
            <p className="text-muted-foreground mt-4 max-w-lg mx-auto">{copy("subtitle", "Explore open positions at Russell's International and our partner organizations.")}</p>
          </div>

          {isLoading ? (
            <div className="grid sm:grid-cols-2 gap-6">
              {[...Array(2)].map((_, i) => <div key={i} className="premium-card h-64 animate-pulse" />)}
            </div>
          ) : jobsList.length === 0 ? null : (
            <div className="grid sm:grid-cols-2 gap-6">
              {jobsList.map((job) => (
              <div key={job.title} className="premium-card p-6 group cursor-pointer" onClick={() => openDrawer(job)}>
                <div className="flex items-start justify-between mb-4">
                  <div className="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                    <Briefcase className="w-5 h-5 text-primary" />
                  </div>
                  <span className={`text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full ${job.type === "Full-Time" ? "bg-blue-50 text-blue-700" : "bg-orange-50 text-orange-700"}`}>
                    {job.type}
                  </span>
                </div>
                <h3 className="font-bold text-foreground font-heading text-base mb-1 group-hover:text-accent transition-colors">{job.title}</h3>
                <p className="text-xs text-muted-foreground mb-3 flex items-center gap-1"><Building2 className="w-3 h-3" /> {job.company}</p>
                <div className="flex flex-wrap gap-3 text-xs text-muted-foreground mb-4">
                  <span className="flex items-center gap-1"><MapPin className="w-3 h-3" /> {job.location}</span>
                  <span className="flex items-center gap-1"><DollarSign className="w-3 h-3" /> {job.salary}</span>
                </div>
                <p className="text-sm text-muted-foreground mb-4">{job.desc}</p>
                <span className="inline-flex items-center gap-1.5 text-sm font-semibold text-accent group-hover:gap-2.5 transition-all">
                  View & Apply <ArrowRight className="w-3.5 h-3.5" />
                </span>
              </div>
              ))}
            </div>
          )}
        </div>
      </section>

      <DetailDrawer open={drawerOpen} onClose={() => setDrawerOpen(false)} title={selected?.title || "Job Details"}>
        {selected && (
          <div className="space-y-6">
            <div className="w-16 h-16 rounded-2xl bg-primary/10 flex items-center justify-center">
              <Briefcase className="w-8 h-8 text-primary" />
            </div>
            <div>
              <h4 className="font-heading font-bold text-xl text-foreground">{selected.title}</h4>
              <p className="text-sm text-muted-foreground mt-1 flex items-center gap-1"><Building2 className="w-3.5 h-3.5" /> {selected.company}</p>
            </div>
            <p className="text-muted-foreground leading-relaxed">{selected.desc}</p>
            <div className="grid grid-cols-2 gap-4">
              {[
                { label: "Location", value: selected.location },
                { label: "Type", value: selected.type },
                { label: "Salary", value: selected.salary },
              ].map((item) => (
                <div key={item.label} className="bg-muted/50 rounded-xl p-4">
                  <div className="text-xs text-muted-foreground mb-1">{item.label}</div>
                  <div className="font-semibold text-foreground text-sm">{item.value}</div>
                </div>
              ))}
            </div>
            <div>
              <h5 className="font-semibold text-foreground mb-3">Requirements</h5>
              <ul className="space-y-2">
                {selected.requirements.map((r) => (
                  <li key={r} className="flex items-start gap-2 text-sm text-muted-foreground">
                    <div className="w-1.5 h-1.5 rounded-full bg-accent mt-1.5 shrink-0" />
                    {r}
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

export default JobsSection;
