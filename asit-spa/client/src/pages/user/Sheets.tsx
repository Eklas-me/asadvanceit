import { useState, useEffect } from 'react';
import { api } from '../../lib/api';
import { useAuthStore } from '../../store/authStore';
import { Link } from 'react-router-dom';
import { 
  Lock,
  Search,
  Eye
} from 'lucide-react';

interface Sheet {
  id: string;
  title: string;
  url: string;
  shift: string;
  slug: string;
  icon: string;
  permission_type: string;
}

export function UserSheets() {
  const { user } = useAuthStore();
  const [sheets, setSheets] = useState<Sheet[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');

  useEffect(() => {
    const fetchSheets = async () => {
      try {
        const { data } = await api.get('/sheets');
        setSheets(data.data);
      } catch (error) {
        console.error('Failed to fetch sheets', error);
      } finally {
        setIsLoading(false);
      }
    };
    fetchSheets();
  }, []);

  const filteredSheets = sheets.filter(s => 
    s.title.toLowerCase().includes(searchTerm.toLowerCase())
  );

  return (
    <div className="space-y-8 animate-in fade-in duration-500">
      <div className="relative overflow-hidden bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
         <div className="absolute top-0 right-0 w-64 h-64 bg-blue-50/50 rounded-full blur-3xl -mr-32 -mt-32"></div>
         <div className="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
              <h2 className="text-3xl font-black tracking-tight text-slate-900 mb-2">Authorized Sheets</h2>
              <p className="text-slate-500 font-medium">
                Access secure spreadsheets for <span className="inline-flex items-center px-2 py-0.5 rounded-lg text-blue-700 bg-blue-50 border border-blue-100 font-bold capitalize">{user?.shift || 'General'}</span> shift.
              </p>
            </div>
            <div className="relative w-full md:w-80">
              <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                <Search className="h-4.5 w-4.5 text-slate-400" />
              </div>
              <input
                type="text"
                className="block w-full pl-11 bg-slate-50/50 border border-slate-200 rounded-2xl py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder-slate-400 font-medium"
                placeholder="Search sheets..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
              />
            </div>
         </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        {isLoading ? (
           Array.from({ length: 6 }).map((_, i) => (
             <div key={i} className="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm animate-pulse">
                <div className="w-14 h-14 bg-slate-100 rounded-2xl mb-6"></div>
                <div className="h-6 bg-slate-100 rounded-full w-3/4 mb-3"></div>
                <div className="h-4 bg-slate-100 rounded-full w-1/2"></div>
             </div>
           ))
        ) : filteredSheets.length === 0 ? (
          <div className="col-span-full bg-white rounded-[2rem] border border-slate-200 border-dashed py-20 text-center">
            <div className="w-20 h-20 bg-slate-50 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-inner">
              <Lock className="w-10 h-10 text-slate-300" />
            </div>
            <h3 className="text-2xl font-bold text-slate-900 mb-2">No Sheets Found</h3>
            <p className="text-slate-500 max-w-sm mx-auto font-medium">
              We couldn&#39;t find any authorized spreadsheets for your account. Please contact your supervisor.
            </p>
          </div>
        ) : (
          filteredSheets.map((sheet) => (
            <div 
              key={sheet.id}
              className="group bg-white rounded-3xl p-6 shadow-sm border border-slate-200 hover:shadow-xl hover:shadow-blue-500/5 hover:border-blue-500 transition-all duration-300 cursor-pointer relative overflow-hidden flex flex-col h-full"
            >
              <div className="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-blue-500/5 to-transparent rounded-bl-full -mr-4 -mt-4 opacity-0 group-hover:opacity-100 transition-opacity"></div>
              
              <div className="flex justify-between items-start mb-6">
                <div className="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center shadow-inner border border-blue-100/50 group-hover:bg-blue-600 group-hover:text-white transition-all duration-300 transform group-hover:scale-110 group-hover:rotate-3">
                   <i className={`${sheet.icon || 'fas fa-file-excel'} text-2xl`}></i>
                </div>
                <span className="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-600 border border-slate-200">
                  {sheet.permission_type.replace('_', ' ')}
                </span>
              </div>
              
              <div className="flex-1">
                <h3 className="text-xl font-black text-slate-900 mb-2 group-hover:text-blue-600 transition-colors line-clamp-1">{sheet.title}</h3>
                <p className="text-slate-500 text-sm font-medium line-clamp-2 mb-4 leading-relaxed italic opacity-80 uppercase tracking-tight">
                   {sheet.shift ? `Shift: ${sheet.shift}` : 'Shared Access'}
                </p>
              </div>

              <div className="mt-auto pt-6">
                <Link 
                  to={`/user/sheets/${sheet.slug}`}
                  className="w-full flex items-center justify-center gap-2 py-4 bg-blue-600 text-white rounded-2xl text-xs font-black hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/20 active:scale-95 group/btn"
                >
                  <Eye className="w-5 h-5 transition-transform group-hover/btn:scale-110" />
                  View Spreadsheet
                </Link>
              </div>
            </div>
          ))
        )}
      </div>
    </div>
  );
}

