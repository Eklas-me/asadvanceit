import { Controller, Get, UseGuards, Request } from '@nestjs/common';
import { PrismaService } from '../../prisma/prisma.service';
import { ShiftService } from '../../services/shift.service';
import { JwtAuthGuard } from '../../auth/guards/jwt-auth.guard';

@Controller('api/user/dashboard')
@UseGuards(JwtAuthGuard)
export class DashboardController {
  constructor(
    private prisma: PrismaService,
    private shiftService: ShiftService,
  ) {}

  @Get('stats')
  async getStats(@Request() req: any) {
    const userId = BigInt(req.user.userId);

    const [today, yesterday, month, lifetime, chart] = await Promise.all([
      this.shiftService.countMyTokensToday(userId),
      this.shiftService.countMyTokensYesterday(userId),
      this.shiftService.countMyTokensMonth(userId),
      this.shiftService.countMyTokensLifetime(userId),
      this.shiftService.getDailyTokenCounts(userId),
    ]);

    return {
      today,
      yesterday,
      month,
      lifetime,
      chart,
    };
  }
}
