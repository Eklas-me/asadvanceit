import { Module } from '@nestjs/common';
import { UserService } from './user.service';
import { DashboardController } from './dashboard/dashboard.controller';
import { TokensController } from './tokens/tokens.controller';
import { PrismaModule } from '../prisma/prisma.module';
import { ShiftService } from '../services/shift.service';

import { SheetsController } from './sheets/sheets.controller';

@Module({
  imports: [PrismaModule],
  providers: [UserService, ShiftService],
  controllers: [DashboardController, TokensController, SheetsController],
})
export class UserModule {}
