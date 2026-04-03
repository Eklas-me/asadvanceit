import { useState, useEffect, useRef } from 'react';
import { useParams, useNavigate, useLocation } from 'react-router-dom';
import { api } from '../../lib/api';
import { 
  ArrowLeft, 
  Maximize2, 
  Minimize2, 
  Loader2, 
  AlertCircle,
  Info
} from 'lucide-react';

interface Sheet {
  id: string;
  title: string;
  url: string;
  slug: string;
}

export function SheetViewer() {
  const { slug } = useParams<{ slug: string }>();
  const navigate = useNavigate();
  const location = useLocation();
  const [sheet, setSheet] = useState<Sheet | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isIframeLoading, setIsIframeLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [isFullscreen, setIsFullscreen] = useState(false);
  const containerRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const fetchSheet = async () => {
      try {
        setIsLoading(true);
        const { data } = await api.get(`/sheets/${slug}`);
        setSheet(data);
      } catch (err: any) {
        console.error('Failed to fetch sheet', err);
        setError(err.response?.data?.message || 'Sheet not found or access denied.');
      } finally {
        setIsLoading(false);
      }
    };
    fetchSheet();
  }, [slug]);

  // Fullscreen toggle logic
  const toggleFullscreen = () => {
    if (!isFullscreen) {
      if (containerRef.current?.requestFullscreen) {
        containerRef.current.requestFullscreen();
      }
    } else {
      if (document.exitFullscreen) {
        document.exitFullscreen();
      }
    }
  };

  useEffect(() => {
    const handleFullscreenChange = () => {
      setIsFullscreen(!!document.fullscreenElement);
    };

    const handleKeyDown = (e: KeyboardEvent) => {
       if (e.key.toLowerCase() === 'f') toggleFullscreen();
    };

    document.addEventListener('fullscreenchange', handleFullscreenChange);
    document.addEventListener('keydown', handleKeyDown);
    
    return () => {
      document.removeEventListener('fullscreenchange', handleFullscreenChange);
      document.removeEventListener('keydown', handleKeyDown);
    };
  }, [isFullscreen]);

  if (isLoading) {
    return (
      <div className="flex flex-col items-center justify-center min-h-[60vh] animate-in fade-in duration-500">
        <Loader2 className="w-12 h-12 text-blue-600 animate-spin mb-4" />
        <p className="text-slate-500 font-bold animate-pulse">Loading secure spreadsheet...</p>
      </div>
    );
  }

  if (error || !sheet) {
    return (
      <div className="flex flex-col items-center justify-center min-h-[60vh] animate-in zoom-in duration-300">
        <div className="w-20 h-20 bg-red-50 text-red-500 rounded-3xl flex items-center justify-center mb-6 shadow-inner border border-red-100">
          <AlertCircle className="w-10 h-10" />
        </div>
        <h2 className="text-2xl font-black text-slate-900 mb-2">Access Denied</h2>
        <p className="text-slate-500 font-medium mb-8 max-w-md text-center">{error}</p>
        <button 
          onClick={() => navigate(location.pathname.startsWith('/admin') ? '/admin/sheets' : '/user/sheets')}
          className="flex items-center gap-2 px-6 py-3 bg-slate-900 text-white rounded-2xl font-black hover:bg-slate-800 transition-all active:scale-95"
        >
          <ArrowLeft className="w-5 h-5" />
          Back to Sheets
        </button>
      </div>
    );
  }

  return (
    <div className="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
      {/* Header */}
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div className="flex items-center gap-4">
          <button 
            onClick={() => navigate(location.pathname.startsWith('/admin') ? '/admin/sheets' : '/user/sheets')}
            className="w-12 h-12 flex items-center justify-center bg-white border border-slate-200 text-slate-600 rounded-2xl hover:bg-slate-50 transition-all active:scale-90 shadow-sm"
          >
            <ArrowLeft className="w-6 h-6" />
          </button>
          <div>
            <h2 className="text-2xl font-black text-slate-900 tracking-tight">{sheet.title}</h2>
            <div className="flex items-center gap-2 text-slate-400 text-xs font-bold uppercase tracking-widest mt-0.5">
               <Info className="w-3.5 h-3.5" />
               <span>Press 'F' for full screen</span>
            </div>
          </div>
        </div>
        
        <div className="flex items-center gap-3">
          <button 
            onClick={toggleFullscreen}
            className="flex items-center gap-2 px-5 py-3 bg-blue-600 text-white rounded-2xl text-xs font-black hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/20 active:scale-95"
          >
            {isFullscreen ? <Minimize2 className="w-4 h-4" /> : <Maximize2 className="w-4 h-4" />}
            {isFullscreen ? 'Exit Fullscreen' : 'Fullscreen'}
          </button>
        </div>
      </div>

      {/* Viewer Container */}
      <div 
        ref={containerRef}
        className={`relative w-full bg-slate-900 rounded-[2rem] overflow-hidden shadow-2xl border-4 border-white transition-all duration-500 ${isFullscreen ? 'fixed inset-0 z-[9999] rounded-none border-0' : 'h-[75vh]'}`}
      >
        {isIframeLoading && (
          <div className="absolute inset-0 flex flex-col items-center justify-center bg-slate-900/50 backdrop-blur-md z-10">
            <Loader2 className="w-10 h-10 text-white animate-spin mb-4" />
            <p className="text-white/70 font-black text-sm uppercase tracking-widest">Connectig to Google...</p>
          </div>
        )}
        
        <iframe
          src={sheet.url}
          className="w-full h-full border-none"
          title={sheet.title}
          allowFullScreen
          onLoad={() => setIsIframeLoading(false)}
        />

        {isFullscreen && (
          <button 
            onClick={toggleFullscreen}
            className="absolute top-6 right-6 w-12 h-12 flex items-center justify-center bg-white/20 hover:bg-white/30 backdrop-blur-xl text-white rounded-2xl border border-white/20 transition-all active:scale-90"
            title="Exit Fullscreen (Esc)"
          >
            <Minimize2 className="w-6 h-6" />
          </button>
        )}
      </div>
    </div>
  );
}
