import { Code, Clock, Users, ArrowRight, BadgeCheck, Crown } from "lucide-react";
import type { ElementType } from "react";
import { useState } from "react";
import { useScrollReveal } from "@/hooks/useScrollReveal";
import DetailDrawer from "@/components/DetailDrawer";
import { useCourses } from "@/hooks/api";
import { resolveIcon } from "@/lib/icons";

type CourseCard = {
  icon: ElementType;
  title: string;
  description: string;
  duration: string;
  students: string;
  tag: string;
  color: string;
  price?: string;
  whatYouLearn: string[];
  highlights: string[];
};

const FeaturedCourses = () => {
  const { ref, visible } = useScrollReveal();
  const [tab, setTab] = useState<"paid" | "navttc">("paid");
  const [drawerOpen, setDrawerOpen] = useState(false);
  const [selectedCourse, setSelectedCourse] = useState<CourseCard | null>(null);

  const { data: paidData, isLoading: paidLoading } = useCourses('paid');
  const { data: navttcData, isLoading: navttcLoading } = useCourses('navttc');

  const mapCourse = (c: import("@/types/api").Course): CourseCard => ({
    ...c,
    icon: resolveIcon(c.icon_name, Code),
    color: c.color_class ?? '',
    description: c.description ?? '',
    students: c.students_count ?? '',
    price: c.price ?? undefined,
    tag: c.tag ?? '',
    whatYouLearn: c.what_you_learn ?? [],
    highlights: c.highlights ?? [],
  });
  const apiPaid   = (paidData?.data   ?? []).map(mapCourse);
  const apiNavttc = (navttcData?.data ?? []).map(mapCourse);

  const courses = tab === "paid" ? apiPaid : apiNavttc;
  const loading = tab === "paid" ? paidLoading : navttcLoading;

  const openDrawer = (course: CourseCard) => {
    setSelectedCourse(course);
    setDrawerOpen(true);
  };

  return (
    <>
      <section id="courses" className="py-20 md:py-28">
        <div
          ref={ref}
          className={`container mx-auto px-4 md:px-8 transition-all duration-700 ${visible ? "opacity-100" : "opacity-0"}`}
        >
          <div className="text-center mb-10">
            <span className="section-label">Featured Programs</span>
            <h2 className="section-title mt-3">Elevate Your Skillset</h2>
            <p className="text-muted-foreground mt-4 max-w-lg mx-auto">Industry-aligned training programs designed to make you job-ready from day one.</p>
          </div>

          {/* Tab Switcher */}
          <div className="flex justify-center mb-10">
            <div className="inline-flex bg-muted rounded-2xl p-1.5 gap-1">
              <button
                onClick={() => setTab("paid")}
                className={`px-6 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300 flex items-center gap-2 ${
                  tab === "paid" ? "bg-background text-foreground shadow-md" : "text-muted-foreground hover:text-foreground"
                }`}
              >
                <Crown className="w-4 h-4" /> Premium Courses
              </button>
              <button
                onClick={() => setTab("navttc")}
                className={`px-6 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300 flex items-center gap-2 ${
                  tab === "navttc" ? "bg-background text-foreground shadow-md" : "text-muted-foreground hover:text-foreground"
                }`}
              >
                <BadgeCheck className="w-4 h-4" /> NAVTTC (Free)
              </button>
            </div>
          </div>

          {/* NAVTTC Trust Badge */}
          {tab === "navttc" && (
            <div className="flex justify-center mb-8 animate-fade-in">
              <div className="inline-flex items-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-2 rounded-full text-sm font-medium">
                <BadgeCheck className="w-4 h-4" /> Government Funded – 100% Free Training Under NAVTTC
              </div>
            </div>
          )}

          {loading ? (
            <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
              {[...Array(3)].map((_, i) => (
                <div key={i} className="premium-card p-6 h-64 animate-pulse" />
              ))}
            </div>
          ) : courses.length === 0 ? null : (
            <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
              {courses.map((c) => (
              <div
                key={c.title}
                className="premium-card p-6 group cursor-pointer"
                onClick={() => openDrawer(c)}
              >
                <div className="flex items-start justify-between mb-5">
                  <div className={`w-12 h-12 rounded-xl ${c.color.split(" ")[0]} flex items-center justify-center group-hover:scale-110 transition-transform duration-300`}>
                    <c.icon className={`w-6 h-6 ${c.color.split(" ")[1]}`} />
                  </div>
                  {c.tag && (
                    <span className={`text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full ${
                      c.tag === "NAVTTC" ? "bg-emerald-50 text-emerald-700" : "bg-accent/10 text-accent"
                    }`}>
                      {c.tag}
                    </span>
                  )}
                </div>
                <h3 className="font-bold text-foreground font-heading text-lg mb-2 group-hover:text-accent transition-colors">{c.title}</h3>
                <div className="flex items-center gap-4 text-xs text-muted-foreground mb-4">
                  <span className="flex items-center gap-1"><Clock className="w-3.5 h-3.5" />{c.duration}</span>
                  <span className="flex items-center gap-1"><Users className="w-3.5 h-3.5" />{c.students} enrolled</span>
                </div>
                {"price" in c && (
                  <div className="font-bold text-foreground text-base mb-4">{c.price}</div>
                )}
                <span className="inline-flex items-center gap-1.5 text-sm font-semibold text-accent group-hover:gap-2.5 transition-all">
                  {tab === "navttc" ? "Apply Now" : "Learn More"} <ArrowRight className="w-3.5 h-3.5" />
                </span>
              </div>
              ))}
            </div>
          )}
        </div>
      </section>

      <DetailDrawer
        open={drawerOpen}
        onClose={() => setDrawerOpen(false)}
        title={selectedCourse?.title || "Course Details"}
      >
        {selectedCourse && (
          <div className="space-y-6">
            <div className={`w-16 h-16 rounded-2xl ${selectedCourse.color.split(" ")[0]} flex items-center justify-center`}>
              <selectedCourse.icon className={`w-8 h-8 ${selectedCourse.color.split(" ")[1]}`} />
            </div>
            <div>
              <h4 className="font-heading font-bold text-xl text-foreground mb-2">{selectedCourse.title}</h4>
              {"price" in selectedCourse && (
                <div className="text-2xl font-bold text-accent mb-1">{selectedCourse.price}</div>
              )}
              {tab === "navttc" && (
                <div className="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 text-sm font-medium px-3 py-1 rounded-full">
                  <BadgeCheck className="w-3.5 h-3.5" /> Free – Government Funded
                </div>
              )}
            </div>
            <div className="flex gap-4 text-sm text-muted-foreground">
              <span className="flex items-center gap-1.5"><Clock className="w-4 h-4" /> {selectedCourse.duration}</span>
              <span className="flex items-center gap-1.5"><Users className="w-4 h-4" /> {selectedCourse.students} enrolled</span>
            </div>
            {selectedCourse.description && (
              <p className="text-muted-foreground leading-relaxed">{selectedCourse.description}</p>
            )}
            {selectedCourse.whatYouLearn.length > 0 && (
              <div>
                <h5 className="font-semibold text-foreground mb-3">What You'll Learn</h5>
                <ul className="space-y-2">
                  {selectedCourse.whatYouLearn.map((item) => (
                    <li key={item} className="flex items-start gap-2 text-sm text-muted-foreground">
                      <div className="w-1.5 h-1.5 rounded-full bg-accent mt-1.5 shrink-0" />
                      {item}
                    </li>
                  ))}
                </ul>
              </div>
            )}
            {selectedCourse.highlights.length > 0 && (
              <div>
                <h5 className="font-semibold text-foreground mb-3">Program Highlights</h5>
                <div className="grid grid-cols-2 gap-3">
                  {selectedCourse.highlights.map((h) => (
                    <div key={h} className="bg-muted/50 rounded-xl p-3 text-center text-sm font-medium text-foreground">{h}</div>
                  ))}
                </div>
              </div>
            )}
          </div>
        )}
      </DetailDrawer>
    </>
  );
};

export default FeaturedCourses;
