import { Controller, Post, Body, UseGuards, Request } from '@nestjs/common';
import { PrismaService } from '../../prisma/prisma.service';
import { JwtAuthGuard } from '../../auth/guards/jwt-auth.guard';

@Controller('api/duplicate-checker')
@UseGuards(JwtAuthGuard)
export class DuplicateCheckerController {
  constructor(private prisma: PrismaService) {}

  @Post()
  async checkDuplicate(@Body('token') token: string) {
    if (!token) {
      return { isDuplicate: false, message: 'No token provided' };
    }

    const existingToken = await this.prisma.live_tokens.findFirst({
      where: { live_token: token },
      select: { id: true, user_name: true, insert_time: true }
    });

    if (existingToken) {
      return {
        isDuplicate: true,
        message: `Already submitted by ${existingToken.user_name} on ${existingToken.insert_time}`,
        data: {
            ...existingToken,
            id: existingToken.id.toString()
        }
      };
    }

    return { isDuplicate: false, message: 'Token is unique' };
  }
}
