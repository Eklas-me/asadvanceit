import { Controller, Get, Post, Body, UseGuards, Request, Query } from '@nestjs/common';
import { PrismaService } from '../../prisma/prisma.service';
import { JwtAuthGuard } from '../../auth/guards/jwt-auth.guard';

@Controller('api/user/tokens')
@UseGuards(JwtAuthGuard)
export class TokensController {
  constructor(private prisma: PrismaService) {}

  @Get()
  async findAll(@Request() req: any, @Query('page') page = 1) {
    const userId = BigInt(req.user.userId);
    const limit = 20;
    const skip = (page - 1) * limit;

    const [tokens, total] = await Promise.all([
      this.prisma.live_tokens.findMany({
        where: { user_id: userId },
        skip,
        take: limit,
        orderBy: { insert_time: 'desc' },
      }),
      this.prisma.live_tokens.count({
        where: { user_id: userId },
      }),
    ]);

    return {
      data: tokens.map(t => ({ 
        ...t, 
        id: t.id.toString(), 
        user_id: t.user_id?.toString(),
        admin_id: t.admin_id?.toString()
      })),
      meta: {
        total,
        page,
        last_page: Math.ceil(total / limit),
      },
    };
  }

  @Post()
  async create(@Request() req: any, @Body('token') token: string) {
    const userId = BigInt(req.user.userId);
    const name = req.user.name;

    const newToken = await this.prisma.live_tokens.create({
      data: {
        user_id: userId,
        user_name: name,
        live_token: token,
        user_type: 'user', // From enum live_tokens_user_type
        insert_time: new Date(),
      },
    });

    return {
      ...newToken,
      id: newToken.id.toString(),
      user_id: newToken.user_id?.toString(),
    };
  }
}
