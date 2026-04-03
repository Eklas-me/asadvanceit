import { Controller, Get, UseGuards, Query } from '@nestjs/common';
import { PrismaService } from '../../prisma/prisma.service';
import { JwtAuthGuard } from '../../auth/guards/jwt-auth.guard';
import { RolesGuard } from '../../auth/guards/roles.guard';
import { Roles } from '../../auth/decorators/roles.decorator';

@Controller('api/admin/dashboard')
@UseGuards(JwtAuthGuard, RolesGuard)
@Roles('admin')
export class DashboardController {
  constructor(private prisma: PrismaService) {}

  @Get('stats')
  async getStats() {
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);

    const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);

    const [
      workers,
      tokensToday,
      tokensYesterday,
      tokensMonth,
      tokensLifetime,
      liveDevices,
      recentTokens
    ] = await Promise.all([
      this.prisma.users.count({ where: { status: 'active' } }),
      this.prisma.live_tokens.count({ where: { insert_time: { gte: today } } }),
      this.prisma.live_tokens.count({ where: { insert_time: { gte: yesterday, lt: today } } }),
      this.prisma.live_tokens.count({ where: { insert_time: { gte: firstDayOfMonth } } }),
      this.prisma.live_tokens.count(),
      this.prisma.users.count({ where: { status: 'online' } }), 
      this.prisma.live_tokens.findMany({
        take: 10,
        orderBy: { insert_time: 'desc' },
        include: { users_live_tokens_user_idTousers: true }
      })
    ]);

    return {
      workers,
      tokens: {
        today: tokensToday,
        yesterday: tokensYesterday,
        month: tokensMonth,
        lifetime: tokensLifetime,
      },
      live_devices: liveDevices,
      recent_tokens: recentTokens.map(t => ({
        user_name: t.users_live_tokens_user_idTousers?.name || t.user_name || 'Unknown',
        live_token: t.live_token,
        status: 'valid', // Default status as it's missing in DB
        insert_time: t.insert_time
      }))
    };
  }

  @Get('workers-report')
  async getWorkersReport(
    @Query('page') page = 1,
    @Query('search') search = ''
  ) {
    const limit = 20;
    const skip = (page - 1) * limit;

    const where = search ? {
      name: { contains: search }
    } : {};

    const [workers, total] = await Promise.all([
      this.prisma.users.findMany({
        where,
        skip,
        take: limit,
        orderBy: { created_at: 'desc' }
      }),
      this.prisma.users.count({ where })
    ]);

    // Serialize BigInt
    const serializedWorkers = workers.map(w => ({
      ...w,
      id: w.id.toString(),
      old_id: w.old_id?.toString()
    }));

    return {
      data: serializedWorkers,
      meta: {
        total,
        page,
        last_page: Math.ceil(total / limit)
      }
    };
  }
}
