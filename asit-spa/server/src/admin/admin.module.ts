import { Module } from '@nestjs/common';
import { DashboardController } from './dashboard/dashboard.controller';
import { WorkersController } from './workers/workers.controller';
import { SettingsController } from './settings/settings.controller';
import { MonitoringController } from './monitoring/monitoring.controller';
import { NotificationsController } from './notifications/notifications.controller';
import { SheetsController } from './sheets/sheets.controller';
import { PrismaModule } from '../prisma/prisma.module';

@Module({
  imports: [PrismaModule],
  controllers: [
    DashboardController,
    WorkersController,
    SettingsController,
    MonitoringController,
    NotificationsController,
    SheetsController,
  ],
})
export class AdminModule {}
