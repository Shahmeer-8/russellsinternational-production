import { Mail, Phone, MapPin, Send } from "lucide-react";
import { useState } from "react";
import { useScrollReveal } from "@/hooks/useScrollReveal";
import { useSettings, useSubmitContact } from "@/hooks/api";
import { useSectionCopy } from "@/hooks/useSectionCopy";

const ContactSection = () => {
  const copy = useSectionCopy("home", "contact");
  const [submitted, setSubmitted] = useState(false);
  const [error, setError] = useState("");
  const { ref, visible } = useScrollReveal();

  const { data: settingsData } = useSettings('contact');
  const settings = settingsData?.data ?? {};
  const phone   = settings['phone']   ?? '+92 304 111 2233';
  const email   = settings['email']   ?? 'info@russellsinternational.com';
  const address = settings['address'] ?? 'Islamabad, Pakistan';

  const { mutate: submitContact, isPending } = useSubmitContact();

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setError("");
    const fd = new FormData(e.currentTarget);
    submitContact(
      { name: fd.get('name') as string, phone: fd.get('phone') as string, email: fd.get('email') as string, interest: fd.get('interest') as string, message: fd.get('message') as string },
      {
        onSuccess: () => setSubmitted(true),
        onError: (err) => setError(err instanceof Error ? err.message : "We couldn't send your message. Please try again."),
      }
    );
  };

  return (
    <section id="contact" className="py-20 md:py-28 bg-section-alt">
      <div
        ref={ref}
        className={`container mx-auto px-4 md:px-8 transition-all duration-700 ${visible ? "opacity-100" : "opacity-0"}`}
      >
        <div className="grid lg:grid-cols-2 gap-12 items-start">
          <div>
            <span className="section-label">{copy("eyebrow", "Get In Touch")}</span>
            <h2 className="section-title mt-3 mb-5">{copy("title", "Ready to Take the Next Step?")}</h2>
            <p className="text-muted-foreground mb-8 leading-relaxed">
              {copy("subtitle", "Fill in the form and our team will get back to you within 24 hours with personalized guidance.")}
            </p>
            <div className="space-y-4">
              {[
                { icon: Phone, label: phone },
                { icon: Mail,  label: email },
                { icon: MapPin, label: address },
              ].map((c) => (
                <div key={c.label} className="flex items-center gap-3">
                  <div className="w-10 h-10 rounded-xl bg-accent/10 flex items-center justify-center">
                    <c.icon className="w-4 h-4 text-accent" />
                  </div>
                  <span className="text-sm text-foreground font-medium">{c.label}</span>
                </div>
              ))}
            </div>
          </div>

          <div className="premium-card p-7 md:p-8">
            {submitted ? (
              <div className="text-center py-10">
                <div className="w-14 h-14 rounded-full bg-green-50 flex items-center justify-center mx-auto mb-4">
                  <Send className="w-7 h-7 text-green-600" />
                </div>
                <h3 className="font-bold text-lg font-heading text-foreground mb-2">Message Sent!</h3>
                <p className="text-sm text-muted-foreground">We'll get back to you within 24 hours.</p>
              </div>
            ) : (
              <form onSubmit={handleSubmit} className="space-y-4">
                <div className="grid sm:grid-cols-2 gap-4">
                  <input name="name" aria-label="Full Name" type="text" placeholder="Full Name" required className="w-full px-4 py-3 rounded-xl bg-muted/50 border border-border text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-accent/30" />
                  <input name="phone" aria-label="Phone Number" type="tel" placeholder="Phone Number" className="w-full px-4 py-3 rounded-xl bg-muted/50 border border-border text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-accent/30" />
                </div>
                <input name="email" aria-label="Email Address" type="email" placeholder="Email Address" required className="w-full px-4 py-3 rounded-xl bg-muted/50 border border-border text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-accent/30" />
                <select name="interest" aria-label="Interest" className="w-full px-4 py-3 rounded-xl bg-muted/50 border border-border text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-accent/30">
                  <option value="">I'm interested in...</option>
                  <option value="IT Training Courses">IT Training Courses</option>
                  <option value="Study Abroad">Study Abroad</option>
                  <option value="Both">Both</option>
                </select>
                <textarea name="message" aria-label="Your Message" placeholder="Your Message" rows={4} className="w-full px-4 py-3 rounded-xl bg-muted/50 border border-border text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-accent/30 resize-none" />
                {error && <p className="text-sm text-destructive" role="alert">{error}</p>}
                <button type="submit" disabled={isPending} className="btn-accent w-full text-base py-3.5 flex items-center justify-center gap-2 disabled:opacity-60">
                  {isPending ? 'Sending…' : 'Send Message'} <Send className="w-4 h-4" />
                </button>
              </form>
            )}
          </div>
        </div>
      </div>
    </section>
  );
};

export default ContactSection;
