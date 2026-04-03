import { useState } from 'react';
import { Outlet, Navigate, Link, useLocation } from 'react-router-dom';
import { useAuthStore } from '../store/authStore';
import { 
  LayoutDashboard, 
  Users, 
  ListTodo, 
  Settings, 
  MonitorPlay,
  Bell,
  LogOut,
  MessageSquare,
  FileSpreadsheet
} from 'lucide-react';

export function AdminLayout() {
  const { user, isAuthenticated, logout } = useAuthStore();
  const location = useLocation();

  if (!isAuthenticated || (user?.role !== 'admin' && user?.role !== 'superadmin')) {
    return <Navigate to="/login" replace />;
  }

  const navigation = [
    { name: 'Dashboard', href: '/admin/dashboard', icon: LayoutDashboard },
    { 
      name: 'Workers', 
      href: '/admin/workers', 
      icon: Users,
      children: [
        { name: 'Active Workers', href: '/admin/workers?status=active' },
        { name: 'Pending Users', href: '/admin/workers?status=pending' },
        { name: 'Suspended Users', href: '/admin/workers?status=suspended' },
        { name: 'Rejected Users', href: '/admin/workers?status=rejected' },
        { name: 'Add Worker', href: '/admin/workers/add' },
      ]
    },
    { name: 'Tokens', href: '/admin/tokens', icon: ListTodo },
    { name: 'Monitoring', href: '/admin/monitoring', icon: MonitorPlay },
    { name: 'Sheets', href: '/admin/sheets', icon: FileSpreadsheet },
    { name: 'Chat', href: '/admin/chat', icon: MessageSquare },
    { name: 'Settings', href: '/admin/settings', icon: Settings },
  ];

  const [expandedItems, setExpandedItems] = useState<string[]>(['Workers']);

  const toggleExpand = (name: string) => {
    setExpandedItems(prev => 
      prev.includes(name) ? prev.filter(i => i !== name) : [...prev, name]
    );
  };

  return (
    <div className="flex h-screen bg-slate-50">
      {/* Sidebar */}
      <div className="hidden md:flex flex-col w-64 bg-slate-900 border-r border-slate-800 shrink-0">
        <div className="h-16 flex items-center px-6 border-b border-slate-800">
          <span className="text-xl font-bold bg-gradient-to-r from-blue-400 to-blue-600 bg-clip-text text-transparent">
            AS-Advance iT
          </span>
        </div>
        
        <div className="flex-1 overflow-y-auto py-4 custom-scrollbar">
          <nav className="px-3 space-y-1">
            {navigation.map((item) => {
              const isActive = location.pathname === item.href || (item.children && location.pathname.startsWith(item.href));
              const isExpanded = expandedItems.includes(item.name);
              
              return (
                <div key={item.name}>
                  {item.children ? (
                    <>
                      <button
                        onClick={() => toggleExpand(item.name)}
                        className={`w-full group flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-lg transition-colors ${
                          isActive 
                            ? 'text-white' 
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white'
                        }`}
                      >
                        <div className="flex items-center">
                          <item.icon className={`mr-3 flex-shrink-0 h-5 w-5 ${isActive ? 'text-blue-400' : 'text-slate-400 group-hover:text-white'}`} />
                          {item.name}
                        </div>
                        <svg
                          className={`ml-2 h-4 w-4 transition-transform ${isExpanded ? 'rotate-90' : ''}`}
                          fill="none"
                          viewBox="0 0 24 24"
                          stroke="currentColor"
                        >
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="9 5l7 7-7 7" />
                        </svg>
                      </button>
                      
                      {isExpanded && (
                        <div className="mt-1 ml-9 space-y-1">
                          {item.children.map((child) => {
                            const isChildActive = location.pathname + location.search === child.href;
                            return (
                              <Link
                                key={child.name}
                                to={child.href}
                                className={`block px-3 py-2 text-sm font-medium rounded-lg transition-colors ${
                                  isChildActive
                                    ? 'bg-blue-600/10 text-blue-400'
                                    : 'text-slate-400 hover:bg-slate-800 hover:text-white'
                                }`}
                              >
                                {child.name}
                              </Link>
                            );
                          })}
                        </div>
                      )}
                    </>
                  ) : (
                    <Link
                      to={item.href}
                      className={`group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors ${
                        isActive 
                          ? 'bg-blue-600 text-white' 
                          : 'text-slate-300 hover:bg-slate-800 hover:text-white'
                      }`}
                    >
                      <item.icon className={`mr-3 flex-shrink-0 h-5 w-5 ${isActive ? 'text-white' : 'text-slate-400 group-hover:text-white'}`} />
                      {item.name}
                    </Link>
                  )}
                </div>
              );
            })}
          </nav>
        </div>

        <div className="p-4 border-t border-slate-800">
          <div className="flex items-center gap-3 px-3 py-2">
            <div className="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold">
              {user.name.charAt(0)}
            </div>
            <div className="flex-1 min-w-0">
              <p className="text-sm font-medium text-white truncate">{user.name}</p>
              <p className="text-xs text-slate-400 truncate">{user.role}</p>
            </div>
          </div>
          <button 
            onClick={logout}
            className="mt-4 w-full flex items-center justify-center gap-2 px-3 py-2 text-sm font-medium text-red-400 hover:bg-red-500/10 rounded-lg transition-colors"
          >
            <LogOut className="w-4 h-4" />
            Sign Out
          </button>
        </div>
      </div>

      {/* Main content */}
      <div className="flex-1 flex flex-col overflow-hidden">
        {/* Top Header */}
        <header className="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6">
          <h1 className="text-xl font-semibold text-slate-800">
            {navigation.find(n => location.pathname.startsWith(n.href))?.name || 'Admin Panel'}
          </h1>
          
          <div className="flex items-center gap-4">
            <button className="relative p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full transition-colors">
              <Bell className="w-5 h-5" />
              <span className="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
            </button>
          </div>
        </header>

        {/* Page Content */}
        <main className="flex-1 overflow-y-auto bg-slate-50 p-6">
          <Outlet />
        </main>
      </div>
    </div>
  );
}
