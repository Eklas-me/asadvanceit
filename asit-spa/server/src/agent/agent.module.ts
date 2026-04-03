import { Module } from '@nestjs/common';
import { AgentAuthController } from './agent-auth/agent-auth.controller';
import { MonitoringController } from './monitoring/monitoring.controller';
import { UpdateController } from './update/update.controller';
import { PrismaModule } from '../prisma/prisma.module';
import { AuthModule } from '../auth/auth.module';
import { TelegramService } from '../services/telegram/telegram.service';

@Module({
  imports: [PrismaModule, AuthModule],
  controllers: [AgentAuthController, MonitoringController, UpdateController],
  providers: [TelegramService]
})
export class AgentModule {}
