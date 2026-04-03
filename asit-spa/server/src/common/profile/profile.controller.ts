import { Controller, Get, Patch, Body, UseGuards, Request } from '@nestjs/common';
import { PrismaService } from '../../prisma/prisma.service';
import { JwtAuthGuard } from '../../auth/guards/jwt-auth.guard';
import * as bcrypt from 'bcrypt';

@Controller('api/profile')
@UseGuards(JwtAuthGuard)
export class ProfileController {
  constructor(private prisma: PrismaService) {}

  @Get()
  async getProfile(@Request() req: any) {
    const user = await this.prisma.users.findUnique({
      where: { id: BigInt(req.user.userId) },
      select: {
        id: true,
        name: true,
        email: true,
        phone: true,
        role: true,
        profile_photo: true,
        shift: true,
        gender: true,
      }
    });
    return { ...user, id: user?.id.toString() };
  }

  @Patch()
  async updateProfile(@Request() req: any, @Body() body: any) {
    const data: any = {
      name: body.name,
      phone: body.phone,
      gender: body.gender,
    };

    if (body.password) {
      data.password = await bcrypt.hash(body.password, 12);
    }

    const updatedUser = await this.prisma.users.update({
      where: { id: BigInt(req.user.userId) },
      data,
      select: {
        id: true,
        name: true,
        email: true,
        phone: true,
        role: true,
        gender: true,
      }
    });

    return { ...updatedUser, id: updatedUser.id.toString() };
  }
}
