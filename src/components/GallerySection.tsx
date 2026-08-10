import { useState } from "react";
import { useScrollReveal } from "@/hooks/useScrollReveal";
import { X, ZoomIn } from "lucide-react";
import { useGallery } from "@/hooks/api";
import { useSectionCopy } from "@/hooks/useSectionCopy";

const GallerySection = () => {
  const copy = useSectionCopy("events", "gallery");
  const { ref, visible } = useScrollReveal();
  const [lightbox, setLightbox] = useState<number | null>(null);

  const { data: galleryData, isLoading } = useGallery();
  const photos = (galleryData?.data ?? []).map((p) => ({ src: p.image_url, alt: p.alt_text, category: p.category }));

  return (
    <>
      <section id="gallery" className="py-20 md:py-28">
        <div
          ref={ref}
          className={`container mx-auto px-4 md:px-8 transition-all duration-700 ${visible ? "opacity-100" : "opacity-0"}`}
        >
          <div className="text-center mb-14">
            <span className="section-label">{copy("eyebrow", "Gallery")}</span>
            <h2 className="section-title mt-3">{copy("title", "Life at Russell's International")}</h2>
            <p className="text-muted-foreground mt-4 max-w-lg mx-auto">{copy("subtitle", "A glimpse into our campus, events, training sessions, and student life.")}</p>
          </div>

          {isLoading ? (
            <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
              {[...Array(6)].map((_, i) => <div key={i} className="rounded-2xl aspect-[4/3] bg-muted animate-pulse" />)}
            </div>
          ) : photos.length === 0 ? null : (
            <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
              {photos.map((photo, i) => (
                <div
                  key={`${photo.src}-${i}`}
                  className="relative group cursor-pointer rounded-2xl overflow-hidden aspect-[4/3]"
                  onClick={() => setLightbox(i)}
                >
                  <img
                    src={photo.src}
                    alt={photo.alt}
                    className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                    loading="lazy"
                    decoding="async"
                    width={960}
                    height={640}
                  />
                  <div className="absolute inset-0 bg-foreground/0 group-hover:bg-foreground/40 transition-colors duration-300 flex items-center justify-center">
                    <div className="opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col items-center gap-2">
                      <ZoomIn className="w-8 h-8 text-white" />
                      <span className="text-white text-sm font-medium">{photo.category}</span>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      </section>

      {lightbox !== null && photos[lightbox] && (
        <div className="fixed inset-0 z-[100] flex items-center justify-center" onClick={() => setLightbox(null)}>
          <div className="absolute inset-0 bg-foreground/80 backdrop-blur-sm animate-fade-in" />
          <div className="relative z-10 max-w-4xl w-full mx-4 animate-scale-in" onClick={(e) => e.stopPropagation()}>
            <button
              onClick={() => setLightbox(null)}
              className="absolute -top-12 right-0 w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center hover:bg-white/20 transition-colors"
              aria-label="Close"
            >
              <X className="w-5 h-5 text-white" />
            </button>
            <img
              src={photos[lightbox].src}
              alt={photos[lightbox].alt}
              className="w-full h-auto rounded-2xl shadow-2xl"
              decoding="async"
            />
            <p className="text-white/70 text-sm text-center mt-3">{photos[lightbox].alt}</p>
          </div>
        </div>
      )}
    </>
  );
};

export default GallerySection;
