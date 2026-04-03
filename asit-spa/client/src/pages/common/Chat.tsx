import { useState, useEffect, useRef } from 'react';
import { useAuthStore } from '../../store/authStore';
import { api } from '../../lib/api';
import { io, Socket } from 'socket.io-client';
import { Search, Send, CheckCheck, Loader2, User } from 'lucide-react';
import { format } from 'date-fns';

interface Message {
  id: string;
  sender_id: string;
  receiver_id: string | null;
  message: string;
  is_read: boolean;
  created_at: string;
  sender?: { name: string; role: string; profile_photo: string | null };
}

interface ChatUser {
  id: string;
  name: string;
  role: string;
  profile_photo: string | null;
  last_message?: Message;
  unread_count?: number;
  is_online?: boolean;  // Derived from socket events if possible
}

export function ChatApp() {
  const { user, token } = useAuthStore();
  const [socket, setSocket] = useState<Socket | null>(null);
  
  const [users, setUsers] = useState<ChatUser[]>([]);
  const [selectedUserId, setSelectedUserId] = useState<string | null>(null);
  const [messages, setMessages] = useState<Message[]>([]);
  const [newMessage, setNewMessage] = useState('');
  
  const [isLoadingUsers, setIsLoadingUsers] = useState(true);
  const [isLoadingMessages, setIsLoadingMessages] = useState(false);
  
  const messagesEndRef = useRef<HTMLDivElement>(null);

  // Initialize Socket.io
  useEffect(() => {
    if (!token) return;
    
    // Connect to the generic socket.io endpoint
    const newSocket = io(import.meta.env.VITE_API_URL || 'http://localhost:3001', {
      auth: { token },
      autoConnect: true,
      transports: ['websocket'],
    });

    setSocket(newSocket);

    newSocket.on('connect', () => {
      console.log('Connected to Chat Socket');
    });

    return () => {
      newSocket.disconnect();
    };
  }, [token]);

  // Handle incoming socket messages
  useEffect(() => {
    if (!socket) return;

    const handleNewMessage = (msg: Message) => {
      // If message belongs to the currently selected conversation, append to list
      if (
        (msg.sender_id === selectedUserId && msg.receiver_id === user?.id) ||
        (msg.sender_id === user?.id && msg.receiver_id === selectedUserId) ||
        (!msg.receiver_id && !selectedUserId) // Global chat
      ) {
        setMessages(prev => [...prev, msg]);
      } else {
        // Just refresh the users list to show updated read counts / last messages
        fetchUsers();
      }
    };

    socket.on('newMessage', handleNewMessage);

    return () => {
      socket.off('newMessage', handleNewMessage);
    };
  }, [socket, selectedUserId, user?.id]);

  // Fetch conversations list
  const fetchUsers = async () => {
    try {
      // For simplicity in this unified component, assume there's a /chat/users endpoint that returns conversational partners
      // Or we can just use the generic workers list if admin.
      const { data } = await api.get('/admin/workers'); 
      // Mapping workers into ChatUser format
      const formatted = data.data.map((u: any) => ({
        id: u.id,
        name: u.name,
        role: u.role,
        profile_photo: u.profile_photo,
        is_online: Boolean(u.last_seen && new Date().getTime() - new Date(u.last_seen).getTime() < 300000)
      }));
      setUsers([{ id: 'global', name: 'Global Announcements', role: 'system', profile_photo: null }, ...formatted]);
    } catch (error) {
      console.error('Failed to fetch chat users', error);
    } finally {
      setIsLoadingUsers(false);
    }
  };

  useEffect(() => {
    fetchUsers();
  }, [user]);

  // Fetch messages for selected conversation
  useEffect(() => {
    if (!selectedUserId) return;
    
    const fetchMessages = async () => {
      setIsLoadingMessages(true);
      try {
        // For project completeness, you'd typically have a Controller for chat history.
        setMessages([]); // Clear while loading placeholder
        
      } catch (error) {
        console.error('Failed to load chat history', error);
      } finally {
        setIsLoadingMessages(false);
      }
    };

    fetchMessages();
  }, [selectedUserId]);

  // Scroll to bottom when messages change
  useEffect(() => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [messages]);

  const sendMessage = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newMessage.trim() || !socket || !selectedUserId) return;

    const payload = {
      receiverId: selectedUserId === 'global' ? null : selectedUserId,
      message: newMessage.trim(),
    };

    socket.emit('sendMessage', payload);
    
    // Optimistic UI update
    const optimisticMsg: Message = {
      id: Math.random().toString(),
      sender_id: user!.id,
      receiver_id: payload.receiverId,
      message: payload.message,
      is_read: false,
      created_at: new Date().toISOString(),
      sender: { name: user!.name, role: user!.role, profile_photo: user!.profile_photo }
    };
    
    setMessages(prev => [...prev, optimisticMsg]);
    setNewMessage('');
  };

  const selectedUser = users.find(u => u.id === selectedUserId);

  return (
    <div className="h-[calc(100vh-8rem)] bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex">
      
      {/* Sidebar - Contacts List */}
      <div className={`${selectedUserId ? 'hidden md:flex' : 'flex'} flex-col w-full md:w-80 border-r border-slate-100 bg-slate-50/50`}>
        <div className="p-4 border-b border-slate-100 bg-white">
          <div className="relative">
            <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
            <input 
              type="text" 
              placeholder="Search conversations..." 
              className="w-full pl-9 pr-4 py-2 bg-slate-100 border border-transparent focus:border-blue-200 focus:bg-white rounded-lg text-sm transition-colors outline-none"
            />
          </div>
        </div>

        <div className="flex-1 overflow-y-auto">
          {isLoadingUsers ? (
            <div className="p-8 flex justify-center"><Loader2 className="w-6 h-6 animate-spin text-blue-500" /></div>
          ) : (
            <div className="divide-y divide-slate-100">
              {users.map((u) => (
                <button
                  key={u.id}
                  onClick={() => setSelectedUserId(u.id)}
                  className={`w-full text-left p-4 flex items-center gap-3 hover:bg-slate-100 transition-colors ${selectedUserId === u.id ? 'bg-blue-50/50 border-l-4 border-blue-500' : 'border-l-4 border-transparent'}`}
                >
                  <div className="relative">
                    {u.profile_photo ? (
                      <img src={u.profile_photo} alt={u.name} className="w-10 h-10 rounded-full object-cover" />
                    ) : (
                      <div className={`w-10 h-10 rounded-full flex items-center justify-center text-white font-medium ${u.id === 'global' ? 'bg-gradient-to-br from-indigo-500 to-purple-600' : 'bg-blue-400'}`}>
                        {u.id === 'global' ? 'G' : u.name.charAt(0)}
                      </div>
                    )}
                    {u.is_online && (
                      <div className="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></div>
                    )}
                  </div>
                  <div className="flex-1 min-w-0">
                    <div className="flex justify-between items-baseline mb-0.5">
                      <h4 className="text-sm font-semibold text-slate-800 truncate">{u.name}</h4>
                      {u.last_message && <span className="text-[10px] text-slate-400">12:30 PM</span>}
                    </div>
                    <p className="text-xs text-slate-500 truncate">
                      {u.id === 'global' ? 'Company wide announcements' : u.role}
                    </p>
                  </div>
                </button>
              ))}
            </div>
          )}
        </div>
      </div>

      {/* Main Chat Area */}
      <div className={`${!selectedUserId ? 'hidden md:flex flex-1 items-center justify-center bg-slate-50' : 'flex-1 flex flex-col bg-white'}`}>
        
        {!selectedUserId ? (
          <div className="text-center">
            <div className="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
              <User className="w-8 h-8" />
            </div>
            <h3 className="text-xl font-medium text-slate-800">Select a conversation</h3>
            <p className="text-slate-500 mt-2">Choose a colleague or channel left to start messaging</p>
          </div>
        ) : (
          <>
            {/* Chat Area Header */}
            <div className="h-16 px-6 border-b border-slate-100 flex items-center justify-between bg-white shadow-sm z-10 font-medium">
              <div className="flex items-center gap-3">
                <button 
                  className="md:hidden p-2 -ml-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100"
                  onClick={() => setSelectedUserId(null)}
                >
                  ←
                </button>
                <div className="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center overflow-hidden">
                   {selectedUser?.profile_photo ? (
                      <img src={selectedUser.profile_photo} alt={selectedUser.name} className="w-full h-full object-cover" />
                    ) : (
                      <span className="text-sm font-bold text-slate-500">{selectedUser?.name.charAt(0)}</span>
                    )}
                </div>
                <div>
                  <h3 className="text-slate-800 font-semibold">{selectedUser?.name}</h3>
                  <p className="text-xs text-green-500 font-medium">{selectedUser?.is_online ? 'Online' : 'Offline'}</p>
                </div>
              </div>
            </div>

            {/* Messages Scroll View */}
            <div className="flex-1 p-6 overflow-y-auto bg-slate-50/50 space-y-4">
              {isLoadingMessages && messages.length === 0 ? (
                 <div className="flex justify-center p-4"><Loader2 className="w-6 h-6 animate-spin text-slate-400" /></div>
              ) : messages.length === 0 ? (
                <div className="text-center p-8">
                  <span className="text-sm bg-slate-200 text-slate-600 px-3 py-1 rounded-full font-medium">
                    This is the beginning of your conversation with {selectedUser?.name}
                  </span>
                </div>
              ) : (
                messages.map((msg, i) => {
                  const isMine = msg.sender_id === user?.id;
                  return (
                    <div key={i} className={`flex flex-col ${isMine ? 'items-end' : 'items-start'}`}>
                      <div className={`max-w-[75%] rounded-2xl px-4 py-2 shadow-sm ${
                        isMine ? 'bg-blue-600 text-white rounded-br-none' : 'bg-white border border-slate-200 text-slate-800 rounded-bl-none'
                      }`}>
                        {selectedUserId === 'global' && !isMine && (
                          <div className="text-[10px] uppercase font-bold text-blue-600 mb-1">{msg.sender?.name}</div>
                        )}
                        <p className="text-sm leading-relaxed whitespace-pre-wrap">{msg.message}</p>
                      </div>
                      <div className="flex items-center gap-1 mt-1 px-1">
                        <span className="text-[10px] text-slate-400 font-medium">
                          {format(new Date(msg.created_at || new Date()), 'hh:mm a')}
                        </span>
                        {isMine && <CheckCheck className="w-3 h-3 text-blue-500" />}
                      </div>
                    </div>
                  );
                })
              )}
              <div ref={messagesEndRef} />
            </div>

            {/* Input Area */}
            <div className="p-4 bg-white border-t border-slate-100">
               <form onSubmit={sendMessage} className="flex gap-2">
                 <input 
                   type="text" 
                   value={newMessage}
                   onChange={(e) => setNewMessage(e.target.value)}
                   placeholder={`Message ${selectedUser?.name}...`}
                   className="flex-1 bg-slate-100 border-transparent rounded-xl px-4 py-3 text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500/50 focus:border-transparent transition-all"
                 />
                 <button 
                   type="submit"
                   disabled={!newMessage.trim()}
                   className="w-12 h-12 flex items-center justify-center bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                 >
                   <Send className="w-5 h-5 ml-1" />
                 </button>
               </form>
            </div>
          </>
        )}
      </div>
    </div>
  );
}
