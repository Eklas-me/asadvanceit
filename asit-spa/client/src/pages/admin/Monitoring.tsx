import { useState, useEffect } from 'react';
import { api } from '../../lib/api';
import { io, Socket } from 'socket.io-client';
import { useAuthStore } from '../../store/authStore';
import { 
  MonitorPlay, 
  Search,
  Activity,
  Maximize2,
  X,
  Wifi,
  WifiOff
} from 'lucide-react';
import { format } from 'date-fns';

interface Device {
  id: string;
  hardware_id: string;
  os_version: string;
  app_version: string;
  last_seen: string;
  status: 'online' | 'offline';
  ip_address: string;
  worker?: {
    name: string;
    shift: string;
  };
}

export function AdminMonitoring() {
  const { token } = useAuthStore();
  const [devices, setDevices] = useState<Device[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  
  // WebRTC / Socket Streaming
  const [socket, setSocket] = useState<Socket | null>(null);
  const [activeStreamId, setActiveStreamId] = useState<string | null>(null);
  const [streamData, setStreamData] = useState<string | null>(null);

  useEffect(() => {
    const fetchDevices = async () => {
      try {
        // We simulate a devices endpoint from the legacy logic
        const { data } = await api.get('/admin/monitoring/devices');
        setDevices(data.data || []);
      } catch (error) {
        console.error('Failed to fetch devices', error);
      } finally {
        setIsLoading(false);
      }
    };
    fetchDevices();
    
    // Poll every 10s for heartbeat updates
    const interval = setInterval(fetchDevices, 10000);
    return () => clearInterval(interval);
  }, []);

  useEffect(() => {
    if (!token) return;
    
    const newSocket = io(import.meta.env.VITE_API_URL || 'http://localhost:3001', {
      auth: { token },
      autoConnect: true,
      transports: ['websocket'],
    });

    setSocket(newSocket);

    newSocket.on('agentDataStream', (payload: { deviceId: string; imageBase64: string }) => {
      if (payload.deviceId === activeStreamId) {
        setStreamData(`data:image/jpeg;base64,${payload.imageBase64}`);
      }
    });

    return () => {
      newSocket.disconnect();
    };
  }, [token, activeStreamId]);

  const startWatching = (deviceId: string) => {
    setActiveStreamId(deviceId);
    setStreamData(null);
    socket?.emit('watchDevice', { deviceId });
  };

  const stopWatching = () => {
    if (activeStreamId) {
      socket?.emit('stopWatchingDevice', { deviceId: activeStreamId });
      setActiveStreamId(null);
      setStreamData(null);
    }
  };

  const filteredDevices = devices.filter(d => 
    d.hardware_id?.toLowerCase().includes(searchTerm.toLowerCase()) || 
    d.worker?.name?.toLowerCase().includes(searchTerm.toLowerCase())
  );

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <div>
          <h2 className="text-2xl font-bold tracking-tight text-slate-800">Live Monitoring</h2>
          <p className="text-slate-500">View real-time agent screen streams and heartbeat status.</p>
        </div>
        <div className="flex items-center gap-2 px-3 py-1.5 bg-green-50 text-green-700 rounded-lg border border-green-200">
          <div className="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
          <span className="text-sm font-medium">{devices.filter(d => d.status === 'online').length} Agents Online</span>
        </div>
      </div>

      <div className="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex items-center justify-between">
        <div className="relative w-full max-w-sm border border-slate-200 rounded-lg bg-slate-50">
          <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <Search className="h-4 w-4 text-slate-400" />
          </div>
          <input
            type="text"
            className="block w-full pl-10 bg-transparent py-2.5 text-sm outline-none text-slate-800 placeholder-slate-400"
            placeholder="Search Hardware ID or Worker name..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
          />
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {isLoading ? (
          <div className="col-span-full py-12 flex justify-center">
             <div className="w-8 h-8 rounded-full border-4 border-blue-500 border-t-transparent animate-spin"></div>
          </div>
        ) : filteredDevices.length === 0 ? (
          <div className="col-span-full bg-white rounded-2xl border border-slate-200 border-dashed p-12 text-center">
            <div className="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
              <Activity className="w-8 h-8 text-slate-400" />
            </div>
            <h3 className="text-lg font-medium text-slate-800 mb-1">No Devices Found</h3>
            <p className="text-slate-500 max-w-sm mx-auto">
              No Tauri agent devices are currently registered or matching your search.
            </p>
          </div>
        ) : (
          filteredDevices.map((device) => (
            <div key={device.id} className="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 relative overflow-hidden group">
              <div className="flex items-start justify-between mb-4">
                <div className="flex items-center gap-3">
                  <div className={`w-10 h-10 rounded-xl flex items-center justify-center ${device.status === 'online' ? 'bg-green-50 text-green-600' : 'bg-slate-100 text-slate-500'}`}>
                    {device.status === 'online' ? <Wifi className="w-5 h-5" /> : <WifiOff className="w-5 h-5" />}
                  </div>
                  <div>
                    <h3 className="font-semibold text-slate-800 line-clamp-1">{device.worker?.name || 'Unassigned Device'}</h3>
                    <p className="text-xs text-slate-500 font-mono tracking-wider">{device.hardware_id}</p>
                  </div>
                </div>
              </div>
              
              <div className="space-y-2 mb-6">
                <div className="flex justify-between text-sm">
                  <span className="text-slate-500">Version</span>
                  <span className="font-medium text-slate-700">v{device.app_version}</span>
                </div>
                <div className="flex justify-between text-sm">
                  <span className="text-slate-500">OS</span>
                  <span className="font-medium text-slate-700">{device.os_version}</span>
                </div>
                <div className="flex justify-between text-sm">
                  <span className="text-slate-500">Last Seen</span>
                  <span className="font-medium text-slate-700">
                    {device.last_seen ? (() => {
                      const d = new Date(device.last_seen);
                      return isNaN(d.getTime()) ? 'Invalid date' : format(d, 'hh:mm:ss a');
                    })() : 'N/A'}
                  </span>
                </div>
              </div>

              <button 
                onClick={() => startWatching(device.hardware_id)}
                disabled={device.status === 'offline'}
                className="w-full flex justify-center items-center gap-2 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-700 hover:border-blue-200 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <MonitorPlay className="w-4 h-4" />
                {device.status === 'online' ? 'Watch Stream' : 'Agent Offline'}
              </button>
            </div>
          ))
        )}
      </div>

      {/* Fullscreen Stream Modal */}
      {activeStreamId && (
        <div className="fixed inset-0 z-50 bg-slate-900/95 backdrop-blur-sm flex items-center justify-center p-4 sm:p-8">
          <div className="bg-black w-full max-w-6xl aspect-video rounded-xl shadow-2xl relative border border-slate-800 overflow-hidden flex flex-col">
            
            <div className="absolute top-0 inset-x-0 h-16 bg-gradient-to-b from-black/80 to-transparent p-4 flex justify-between items-start z-10 pointer-events-none">
              <div className="flex items-center gap-3">
                <div className="w-3 h-3 rounded-full bg-red-500 animate-pulse"></div>
                <span className="text-white font-medium text-shadow">LIVE: {activeStreamId}</span>
              </div>
              <button 
                onClick={stopWatching}
                className="p-2 bg-white/10 hover:bg-red-500/20 text-white rounded-lg backdrop-blur-md transition-colors pointer-events-auto"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <div className="flex-1 w-full h-full flex items-center justify-center bg-slate-900">
              {streamData ? (
                <img src={streamData} alt="Agent Screen Stream" className="w-full h-full object-contain" />
              ) : (
                <div className="flex flex-col items-center justify-center text-slate-400">
                  <div className="w-12 h-12 rounded-full border-4 border-slate-600 border-t-blue-500 animate-spin mb-4"></div>
                  <p className="font-medium animate-pulse">Connecting to WebRTC Signaling...</p>
                  <p className="text-sm mt-2">Waiting for agent base64 frames</p>
                </div>
              )}
            </div>
            
            <div className="absolute bottom-4 right-4 pointer-events-auto">
              <button 
                className="p-3 bg-black/50 hover:bg-black/80 text-white rounded-lg backdrop-blur-md transition-colors border border-white/10"
                onClick={() => document.documentElement.requestFullscreen().catch(() => {})}
              >
                <Maximize2 className="w-5 h-5" />
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
