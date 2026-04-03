import { Module } from '@nestjs/common';
import { ProfileController } from './profile/profile.controller';
import { SheetsController } from './sheets/sheets.controller';
import { DuplicateCheckerController } from './duplicate-checker/duplicate-checker.controller';

import { PrismaModule } from '../prisma/prisma.module';

@Module({
  imports: [PrismaModule],
  controllers: [ProfileController, SheetsController, DuplicateCheckerController]
})
export class CommonModule {}
