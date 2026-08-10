import { Star, Quote, PlayCircle } from "lucide-react";
import { useScrollReveal } from "@/hooks/useScrollReveal";
import { useTestimonials } from "@/hooks/api";
import { useSectionCopy } from "@/hooks/useSectionCopy";
import {
  Carousel,
  CarouselContent,
  CarouselItem,
  CarouselNext,
  CarouselPrevious,
} from "@/components/ui/carousel";

const UNAVAILABLE_YOUTUBE_IDS = new Set(["ysz5S6PUM-U"]);

const extractYoutubeId = (value: string) => {
  const trimmed = value.trim();

  if (/^[A-Za-z0-9_-]{11}$/.test(trimmed)) {
    return trimmed;
  }

  const match = trimmed.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?.*v=|embed\/|shorts\/))([A-Za-z0-9_-]{11})/);

  return match?.[1] ?? trimmed;
};

const Testimonials = () => {
  const copy = useSectionCopy("home", "testimonials");
  const { ref, visible } = useScrollReveal();

  const { data: testimonialsData, isLoading } = useTestimonials();
  const allApi = testimonialsData?.data ?? [];
  const apiWritten = allApi
    .filter((t) => t.type === "written")
    .map((t) => ({ name: t.name, program: t.program, text: t.quote ?? "", image: t.image_url }));
  const apiVideos = allApi
    .filter((t) => t.type === "video" && t.youtube_id)
    .map((t) => ({ id: extractYoutubeId(t.youtube_id ?? ""), name: t.name, program: t.program }))
    .filter((t) => t.id && !UNAVAILABLE_YOUTUBE_IDS.has(t.id));

  const writtenList = apiWritten;
  const videoList = apiVideos;

  const renderVideoCard = (v: typeof videoList[number]) => (
    <div key={v.id} className="premium-card h-full overflow-hidden group">
      <div className="relative aspect-video bg-muted">
        <iframe
          src={`https://www.youtube.com/embed/${v.id}`}
          title={`${v.name} testimonial`}
          loading="lazy"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
          referrerPolicy="strict-origin-when-cross-origin"
          allowFullScreen
          className="absolute inset-0 w-full h-full"
        />
      </div>
      <div className="p-5 flex items-center justify-between">
        <div>
          <div className="font-heading font-bold text-foreground">{v.name}</div>
          <div className="text-xs text-muted-foreground">{v.program}</div>
        </div>
        <div className="flex items-center gap-1.5 text-accent text-xs font-semibold">
          <PlayCircle className="w-4 h-4" /> Video Story
        </div>
      </div>
    </div>
  );

  const renderWrittenCard = (r: typeof writtenList[number]) => (
    <div key={r.name} className="premium-card h-full p-7 relative group">
      <Quote className="w-8 h-8 text-muted/80 absolute top-6 right-6" />
      <div className="flex gap-1 mb-4">
        {[...Array(5)].map((_, i) => (
          <Star key={i} className="w-4 h-4 fill-amber-400 text-amber-400" />
        ))}
      </div>
      <p className="text-sm text-muted-foreground leading-relaxed mb-6">"{r.text}"</p>
      <div className="flex items-center gap-3">
        {r.image && <img src={r.image} alt={r.name} className="w-12 h-12 rounded-full object-cover border-2 border-border" loading="lazy" decoding="async" width={512} height={512} />}
        <div>
          <div className="font-semibold text-foreground text-sm">{r.name}</div>
          <div className="text-xs text-muted-foreground">{r.program}</div>
        </div>
      </div>
    </div>
  );

  return (
    <section id="testimonials" className="py-20 md:py-28">
      <div
        ref={ref}
        className={`container mx-auto px-4 md:px-8 transition-all duration-700 ${visible ? "opacity-100" : "opacity-0"}`}
      >
        <div className="text-center mb-14">
          <span className="section-label">{copy("eyebrow", "Student Stories")}</span>
          <h2 className="section-title mt-3">{copy("title", "Real Success, Real People")}</h2>
          <p className="text-muted-foreground mt-4 max-w-lg mx-auto">{videoList.length > 0 ? "Watch and read how our students transformed their futures with us." : "Read how our students transformed their futures with us."}</p>
        </div>

        {isLoading ? (
          <div className="grid md:grid-cols-3 gap-6">
            {[...Array(3)].map((_, i) => <div key={i} className="premium-card h-64 animate-pulse" />)}
          </div>
        ) : videoList.length > 2 && (
          <Carousel opts={{ align: "start", loop: true }} className="relative mb-8">
            <CarouselContent className="-ml-4">
              {videoList.map((v) => (
                <CarouselItem key={v.id} className="pl-4 basis-full md:basis-1/2">
                  {renderVideoCard(v)}
                </CarouselItem>
              ))}
            </CarouselContent>
            <CarouselPrevious className="hidden md:flex -left-4" />
            <CarouselNext className="hidden md:flex -right-4" />
          </Carousel>
        )}

        {!isLoading && videoList.length > 0 && videoList.length <= 2 && (
          <div className="grid md:grid-cols-2 gap-6 mb-8">
            {videoList.map(renderVideoCard)}
          </div>
        )}

        {!isLoading && writtenList.length > 3 && (
          <Carousel opts={{ align: "start", loop: true }} className="relative">
            <CarouselContent className="-ml-4">
              {writtenList.map((r) => (
                <CarouselItem key={r.name} className="pl-4 basis-full md:basis-1/2 lg:basis-1/3">
                  {renderWrittenCard(r)}
                </CarouselItem>
              ))}
            </CarouselContent>
            <CarouselPrevious className="hidden md:flex -left-4" />
            <CarouselNext className="hidden md:flex -right-4" />
          </Carousel>
        )}

        {!isLoading && writtenList.length > 0 && writtenList.length <= 3 && (
          <div className="grid md:grid-cols-3 gap-6">
            {writtenList.map(renderWrittenCard)}
          </div>
        )}
      </div>
    </section>
  );
};

export default Testimonials;
