import {
  WebSocketGateway,
  SubscribeMessage,
  MessageBody,
  ConnectedSocket,
  WebSocketServer,
} from '@nestjs/websockets';
import { Server, Socket } from 'socket.io';
import { JwtService } from '@nestjs/jwt';

@WebSocketGateway({
  cors: {
    origin: '*',
  },
})
export class MonitoringGateway {
  @WebSocketServer()
  server: Server;

  constructor(private jwtService: JwtService) {}

  // Middleware-like verification inside handlers due to agent specific token handling
  private verifyClient(client: Socket) {
    const token = client.handshake.auth.token || client.handshake.headers.authorization?.split(' ')[1];
    if (!token) return null;
    try {
      return this.jwtService.verify(token);
    } catch {
      return null;
    }
  }

  @SubscribeMessage('agentDataStream')
  handleAgentStream(
    @MessageBody() payload: { deviceId: string; imageBase64: string },
    @ConnectedSocket() client: Socket,
  ) {
    const user = this.verifyClient(client);
    if (!user) {
      client.disconnect();
      return;
    }

    // Broadcast stream to admins watching this device
    this.server.to(`watch_${payload.deviceId}`).emit('agentDataStream', {
      deviceId: payload.deviceId,
      imageBase64: payload.imageBase64,
    });
  }

  @SubscribeMessage('watchDevice')
  handleWatchDevice(
    @MessageBody() payload: { deviceId: string },
    @ConnectedSocket() client: Socket,
  ) {
    const user = this.verifyClient(client);
    if (user && user.role === 'admin') {
      client.join(`watch_${payload.deviceId}`);
    }
  }

  @SubscribeMessage('stopWatchingDevice')
  handleStopWatchingDevice(
    @MessageBody() payload: { deviceId: string },
    @ConnectedSocket() client: Socket,
  ) {
    client.leave(`watch_${payload.deviceId}`);
  }

  @SubscribeMessage('webrtcSignal')
  handleWebRTCSignal(
    @MessageBody() payload: { targetId: string; signal: any; type: string, fromAdmin?: boolean },
    @ConnectedSocket() client: Socket,
  ) {
    // Relay WebRTC signaling data between Agent and Admin
    // Target could be the hardware ID of the agent, or the socket ID of the admin
    this.server.emit('webrtcSignal', {
      ...payload,
      senderSocketId: client.id
    });
  }
}
