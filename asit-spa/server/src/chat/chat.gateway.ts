import {
  WebSocketGateway,
  SubscribeMessage,
  MessageBody,
  ConnectedSocket,
  WebSocketServer,
  OnGatewayConnection,
  OnGatewayDisconnect,
} from '@nestjs/websockets';
import { Server, Socket } from 'socket.io';
import { JwtService } from '@nestjs/jwt';
import { PrismaService } from '../prisma/prisma.service';

@WebSocketGateway({
  cors: {
    origin: '*',
  },
})
export class ChatGateway implements OnGatewayConnection, OnGatewayDisconnect {
  @WebSocketServer()
  server: Server;

  // Map of userId -> Set of Socket IDs
  private connectedUsers = new Map<string, Set<string>>();

  constructor(
    private jwtService: JwtService,
    private prisma: PrismaService,
  ) {}

  async handleConnection(client: Socket) {
    try {
      const token = client.handshake.auth.token?.split(' ')[1] || client.handshake.headers.authorization?.split(' ')[1];
      if (!token) {
        client.disconnect();
        return;
      }

      const decoded = this.jwtService.verify(token);
      client.data.user = decoded;
      
      const userId = decoded.sub;
      if (!this.connectedUsers.has(userId)) {
        this.connectedUsers.set(userId, new Set());
      }
      this.connectedUsers.get(userId)?.add(client.id);

      // Join personal room
      client.join(`user_${userId}`);
      
      // If admin, join admins room for notifications
      if (decoded.role === 'admin') {
        client.join('admins');
      }

    } catch (e) {
      client.disconnect();
    }
  }

  handleDisconnect(client: Socket) {
    const userId = client.data?.user?.sub;
    if (userId) {
      this.connectedUsers.get(userId)?.delete(client.id);
      if (this.connectedUsers.get(userId)?.size === 0) {
        this.connectedUsers.delete(userId);
      }
    }
  }

  @SubscribeMessage('sendMessage')
  async handleMessage(
    @MessageBody() payload: { receiverId: string; receiverType: string; message: string },
    @ConnectedSocket() client: Socket,
  ) {
    const sender = client.data.user;

    const savedMsg = await this.prisma.messages.create({
      data: {
        sender_id: BigInt(sender.sub),
        sender_type: sender.role,
        receiver_id: BigInt(payload.receiverId),
        receiver_type: payload.receiverType,
        message: payload.message,
      }
    });

    const serializedMsg = {
      ...savedMsg,
      id: savedMsg.id.toString(),
      sender_id: savedMsg.sender_id.toString(),
      receiver_id: savedMsg.receiver_id.toString(),
    };

    // Emit to receiver's personal room
    this.server.to(`user_${payload.receiverId}`).emit('newMessage', serializedMsg);
    
    // Also emit back to sender to confirm
    client.emit('messageSent', serializedMsg);
    
    return serializedMsg;
  }
}
