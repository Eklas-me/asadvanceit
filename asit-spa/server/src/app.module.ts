import { Module } from '@nestjs/common';
import { ConfigModule } from '@nestjs/config';
import { AppController } from './app.controller';
import { AppService } from './app.service';
import { PrismaModule } from './prisma/prisma.module';
import { AuthModule } from './auth/auth.module';
import { GlobalJwtModule } from './auth/global-jwt.module';
import { AdminModule } from './admin/admin.module';
import { UserModule } from './user/user.module';
import { CommonModule } from './common/common.module';
import { ShiftService } from './services/shift/shift.service';
import { AgentModule } from './agent/agent.module';
import { TelegramService } from './services/telegram/telegram.service';
import { ChatModule } from './chat/chat.module';
import { RealtimeModule } from './realtime/realtime.module';

@Module({
  imports: [
    ConfigModule.forRoot({ isGlobal: true }),
    PrismaModule,
    GlobalJwtModule,
    AuthModule,
    AdminModule,
    UserModule,
    CommonModule,
    AgentModule,
    ChatModule,
    RealtimeModule,
  ],
  controllers: [AppController],
  providers: [AppService, ShiftService, TelegramService],
})
export class AppModule {}
