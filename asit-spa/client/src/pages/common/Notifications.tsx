import { useState, useEffect } from 'react';
import { api } from '../../lib/api';
import { Bell, Copy, CheckCircle2, ShieldAlert, FileText, Info } from 'lucide-react';
import { format } from 'date-fns';

interface Notification {
  id: string;
  type: string;
  title: string;
  message: string;
  is_read: boolean;
  created_at: string;
}

export function Notifications() {
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const fetchNotifications = async () => {
      try {
        const { data } = await api.get('/notifications');
        setNotifications(data.data || []);
      } catch (error) {
        console.error('Failed to fetch notifications', error);
      } finally {
        setIsLoading(false);
      }
    };
    fetchNotifications();
  }, []);

  const markAsRead = async (id: string) => {
    try {
      await api.put(`/notifications/${id}/read`);
      setNotifications(prev => prev.map(n => n.id === id ? { ...n, is_read: true } : n));
    } catch (error) {
      console.error('Failed to mark notification as read');
    }
  };

  const getIcon = (type: string) => {
    switch (type) {
      case 'token_duplicate': return <Copy className="w-5 h-5 text-amber-500" />;
      case 'system_alert': return <ShieldAlert className="w-5 h-5 text-red-500" />;
      case 'sheet_updated': return <FileText className="w-5 h-5 text-blue-500" />;
      case 'success': return <CheckCircle2 className="w-5 h-5 text-green-500" />;
      default: return <Info className="w-5 h-5 text-indigo-500" />;
    }
  };

  return (
    <div className="max-w-3xl mx-auto space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-2xl font-bold tracking-tight text-slate-800">Notifications</h2>
          <p className="text-slate-500">Stay updated on alerts, system messages, and tokens.</p>
        </div>
        <div className="bg-blue-100 text-blue-700 px-4 py-2 rounded-xl font-bold font-mono">
          {notifications.filter(n => !n.is_read).length} Unread
        </div>
      </div>

      <div className="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        {isLoading ? (
           <div className="p-12 flex justify-center">
             <div className="w-8 h-8 rounded-full border-4 border-blue-500 border-t-transparent animate-spin"></div>
           </div>
        ) : notifications.length === 0 ? (
          <div className="p-16 text-center text-slate-400">
            <Bell className="w-12 h-12 mx-auto mb-4 opacity-20" />
            <h3 className="text-lg font-medium text-slate-600 mb-1">All Caught Up!</h3>
            <p>You have no notifications at this time.</p>
          </div>
        ) : (
          <div className="divide-y divide-slate-100">
            {notifications.map((notification) => (
              <div 
                key={notification.id} 
                className={`p-6 transition-colors hover:bg-slate-50 cursor-pointer ${!notification.is_read ? 'bg-blue-50/30' : ''}`}
                onClick={() => !notification.is_read && markAsRead(notification.id)}
              >
                 <div className="flex gap-4">
                   <div className={`mt-1 w-10 h-10 rounded-full flex items-center justify-center shrink-0 ${!notification.is_read ? 'bg-white shadow-sm border border-slate-200' : 'bg-slate-50'}`}>
                     {getIcon(notification.type)}
                   </div>
                   <div className="flex-1">
                     <div className="flex justify-between items-start mb-1">
                       <h4 className={`text-sm font-semibold ${!notification.is_read ? 'text-slate-900' : 'text-slate-700'}`}>
                         {notification.title}
                       </h4>
                       <span className="text-xs font-medium text-slate-400 whitespace-nowrap ml-4">
                         {format(new Date(notification.created_at), 'MMM d, h:mm a')}
                       </span>
                     </div>
                     <p className={`text-sm ${!notification.is_read ? 'text-slate-700 font-medium' : 'text-slate-500'}`}>
                       {notification.message}
                     </p>
                   </div>
                   {!notification.is_read && (
                     <div className="w-2.5 h-2.5 bg-blue-600 rounded-full shrink-0 mt-2"></div>
                   )}
                 </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
